import 'dart:math';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:sipreti/pages/attendance_page.dart';
import 'package:tflite_flutter/tflite_flutter.dart';
import 'package:image/image.dart' as img;
import 'package:hive_flutter/hive_flutter.dart';
import 'package:sipreti/services/api_service.dart';

class Biometric2Page extends StatefulWidget {
  const Biometric2Page({super.key});

  @override
  State<Biometric2Page> createState() => _Biometric2PageState();
}

class _Biometric2PageState extends State<Biometric2Page> {
  CameraController? _cameraController;
  List<CameraDescription>? cameras;
  XFile? capturedImage;
  Interpreter? _interpreter;
  final ApiService _apiService = ApiService();

  bool _showInstructionCard = true;
  // bool _cameraButtonEnabled = false;
  late FaceDetector _faceDetector;
  bool _isProcessing = false;
  bool _cameraStopped = false;

  final List<String> instructions = [
    "Kedipkan mata",
    "Tersenyum ke Kamera",
    // "Menoleh ke Kiri",
    // "Menoleh ke Kanan"
  ];

  late String currentInstruction;

  String? urlFoto;
  final String baseUrl = 'http://35.187.225.70/sipreti/uploads/foto_pegawai/';

  @override
  void initState() {
    super.initState();
    _initializeCamera();
    _loadModel();
    _faceDetector = FaceDetector(
      options: FaceDetectorOptions(
        enableClassification: true,
        enableLandmarks: true,
        enableTracking: true,
        enableContours: true,
        performanceMode: FaceDetectorMode.fast,
      ),
    );
    _selectRandomInstruction();
    var pegawaiBox = Hive.box('pegawai');
    setState(() {
      urlFoto = pegawaiBox.get('url_foto');
    });
  }

  void _selectRandomInstruction() {
    final random = Random();
    currentInstruction = instructions[random.nextInt(instructions.length)];
  }

  Future<void> verifyFace(String idPegawai, List<double> faceEmbeddings) async {
    Map<String, dynamic> result = await _apiService.faceVerification(
      idPegawai,
      faceEmbeddings,
    );

    if (!mounted) return;

    if (result["success"] == false) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result["message"] ?? "Terjadi kesalahan")),
      );
    } else {
      debugPrint(result.toString());
    }
  }

  InputImageRotation _rotationFromCamera(int sensorOrientation) {
    switch (sensorOrientation) {
      case 90:
        return InputImageRotation.rotation90deg;
      case 180:
        return InputImageRotation.rotation180deg;
      case 270:
        return InputImageRotation.rotation270deg;
      case 0:
      default:
        return InputImageRotation.rotation0deg;
    }
  }

  Future<void> _processCameraImage(CameraImage image) async {
    if (_isProcessing || !_showInstructionCard) return;
    _isProcessing = true;

    try {
      final WriteBuffer allBytes = WriteBuffer();
      for (var plane in image.planes) {
        allBytes.putUint8List(plane.bytes);
      }

      final bytes = allBytes.done().buffer.asUint8List();
      final Size imageSize =
          Size(image.width.toDouble(), image.height.toDouble());
      final imageRotation =
          _rotationFromCamera(_cameraController!.description.sensorOrientation);

      final inputImage = InputImage.fromBytes(
        bytes: bytes,
        inputImageData: InputImageData(
          size: imageSize,
          imageRotation: imageRotation,
          inputImageFormat: InputImageFormat.nv21,
          planeData: image.planes
              .map(
                (plane) => InputImagePlaneMetadata(
                  bytesPerRow: plane.bytesPerRow,
                  height: plane.height,
                  width: plane.width,
                ),
              )
              .toList(),
        ),
      );

      final List<Face> faces = await _faceDetector.processImage(inputImage);

      if (faces.isNotEmpty) {
        final face = faces.first;

        switch (currentInstruction) {
          case "Kedipkan mata":
            if ((face.leftEyeOpenProbability ?? 1.0) < 0.2 &&
                (face.rightEyeOpenProbability ?? 1.0) < 0.2) {
              _onInstructionCompleted();
            }
            break;
          case "Tersenyum ke Kamera":
            if ((face.smilingProbability ?? 0.0) > 0.7) {
              _onInstructionCompleted();
            }
            break;
          case "Menoleh ke Kiri":
            if ((face.headEulerAngleY ?? 0.0) > 20) {
              _onInstructionCompleted();
            }
            break;
          case "Menoleh ke Kanan":
            if ((face.headEulerAngleY ?? 0.0) < -20) {
              _onInstructionCompleted();
            }
            break;
        }
      }
    } catch (e) {
      debugPrint("Face processing error: $e");
    } finally {
      await Future.delayed(const Duration(milliseconds: 300));
      _isProcessing = false;
    }
  }

  void _onInstructionCompleted() async {
    setState(() {
      _showInstructionCard = false;
    });
    await _captureImage();

    _cameraController?.stopImageStream();
  }

  Future<void> _initializeCamera() async {
    cameras = await availableCameras();
    if (cameras != null && cameras!.isNotEmpty) {
      _cameraController = CameraController(
        cameras![1],
        ResolutionPreset.high,
      );

      await _cameraController!.initialize();
      _cameraController!.startImageStream(_processCameraImage);
      setState(() {});
    }
  }

  Future<void> _loadModel() async {
    try {
      _interpreter = await Interpreter.fromAsset('model/mobilefacenet.tflite');
    } catch (e) {
      debugPrint("Error loading model: $e");
    }
  }

  double manhattanDistance(List<double> e1, List<double> e2) {
    double sum = 0.0;
    for (int i = 0; i < e1.length; i++) {
      sum += (e1[i] - e2[i]).abs();
    }
    return sum;
  }

  Future<void> _captureImage() async {
    _showLoadingDialog();

    if (_cameraController != null && _cameraController!.value.isInitialized) {
      try {
        final image = await _cameraController!.takePicture();
        setState(() {
          capturedImage = image;
          _cameraStopped = true;
        });

        await _detectAndCropFace(image);
      } catch (e) {
        debugPrint("Error capturing image: $e");
      }
    }
  }

  Future<void> _detectAndCropFace(XFile image) async {
    final totalTime = Stopwatch()..start();

    final inputImage = InputImage.fromFilePath(image.path);
    final faces = await _faceDetector.processImage(inputImage);

    if (faces.isNotEmpty) {
      final face = faces.first;
      final croppedFace = await _cropFace(image, face.boundingBox);

      final embeddings = await _getFaceEmbeddings(croppedFace);
      var pegawaiBox = Hive.box('pegawai');

      // String idPegawai = pegawaiBox.get('id_pegawai');
      List<dynamic> faceEmbeddings = pegawaiBox.get('face_embeddings');

      List<double> distances2 = [];

      for (int i = 0; i < faceEmbeddings.length; i++) {
        List<double> storedEmbedding = List<double>.from(faceEmbeddings[i]);
        double distance = manhattanDistance(embeddings, storedEmbedding);
        distances2.add(distance);
      }

      // Cek apakah ada jarak di bawah threshold (misal 7)
      const double threshold = 7;
      bool verifikasi = distances2.any((d) => d < threshold);

      String message =
          verifikasi ? "Wajah terverifikasi" : "Wajah tidak terverifikasi";
      int value = verifikasi ? 1 : 0;

      // Tampilkan hasil akhir
      debugPrint('Jarak Kedekatan: $distances2');
      debugPrint('message: $message');
      debugPrint('Value: $value');

      final presensiBox = await Hive.openBox('presensi');
      await presensiBox.put('face_status', value);

      totalTime.stop();
      final verificationTime = '${totalTime.elapsedMicroseconds} µs | '
          '${totalTime.elapsedMilliseconds} ms | '
          '${(totalTime.elapsedMilliseconds / 1000).toStringAsFixed(3)} s';

      await presensiBox.put('verification_time', verificationTime);

      if (mounted) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => AttendancePage(
              capturedImage: capturedImage,
            ),
          ),
        );
      }
    } else {
      _showCapturedImageDialog();
      Future.delayed(const Duration(seconds: 3), () {
        if (mounted) {
          Navigator.pushNamed(context, '/biometric');
        }
      });
    }
  }

  Future<img.Image> _cropFace(XFile image, Rect boundingBox) async {
    final bytes = await image.readAsBytes();
    final originalImage = img.decodeImage(bytes);
    if (originalImage == null) {
      throw Exception("Error reading the original image");
    }

    final int left = boundingBox.left.toInt().clamp(0, originalImage.width);
    final int top = boundingBox.top.toInt().clamp(0, originalImage.height);
    final int width =
        boundingBox.width.toInt().clamp(0, originalImage.width - left);
    final int height =
        boundingBox.height.toInt().clamp(0, originalImage.height - top);
    final cropped = img.copyCrop(originalImage, left, top, width, height);

    return cropped;
  }

  Future<List<double>> _getFaceEmbeddings(img.Image faceImage) async {
    Float32List input = await _loadAndNormalizeImage(faceImage);

    final Stopwatch reshapeTime = Stopwatch()..start();
    var reshapedInput = input.buffer.asFloat32List().reshape([1, 112, 112, 3]);
    reshapeTime.stop();

    var output =
        List<List<double>>.generate(1, (_) => List<double>.filled(192, 0.0));
    _interpreter?.run(reshapedInput, output);

    return output[0];
  }

  Future<Float32List> _loadAndNormalizeImage(img.Image image) async {
    final resizedImage = img.copyResize(image, width: 112, height: 112);

    final pixels = resizedImage.getBytes(format: img.Format.rgb);
    final floatPixels = Float32List(pixels.length);

    for (int i = 0; i < pixels.length; i++) {
      floatPixels[i] = pixels[i] / 255.0;
    }

    return floatPixels;
  }

  void _showCapturedImageDialog() {
    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (BuildContext context) {
        return Dialog(
          backgroundColor: Colors.red.withOpacity(0.5),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          child: const Padding(
            padding: EdgeInsets.all(16.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  "No face detected, please try again.",
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _showLoadingDialog() {
    showGeneralDialog(
      context: context,
      barrierDismissible: false,
      barrierLabel: "Loading",
      pageBuilder: (_, __, ___) {
        return Center(
          child: Material(
            color: Colors.transparent,
            child: Card(
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16)),
              elevation: 8,
              color: Colors.white,
              child: const Padding(
                padding: EdgeInsets.symmetric(horizontal: 24.0, vertical: 32.0),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircularProgressIndicator(),
                    SizedBox(height: 20),
                    Text(
                      "Harap Tunggu",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
      transitionBuilder: (_, anim, __, child) {
        return FadeTransition(
          opacity: CurvedAnimation(parent: anim, curve: Curves.easeOut),
          child: child,
        );
      },
      transitionDuration: const Duration(milliseconds: 250),
    );
  }

  @override
  void dispose() {
    _cameraController?.dispose();
    _faceDetector.close();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    double screenWidth = MediaQuery.of(context).size.width;
    double screenHeight = MediaQuery.of(context).size.height;

    return Scaffold(
        appBar: AppBar(
          backgroundColor: Colors.blue,
          title: const Text(
            "Verifikasi Wajah",
            style: TextStyle(
                fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
          ),
          automaticallyImplyLeading: false,
          actions: [
            Row(
              children: [
                Image.asset(
                  'assets/images/pemkot_malang_logo.png',
                  height: 32,
                ),
                const SizedBox(width: 8),
                CircleAvatar(
                  backgroundImage: urlFoto != null
                      ? NetworkImage(baseUrl + urlFoto!)
                      : const AssetImage('assets/images/default_profile.png')
                          as ImageProvider,
                  radius: 16,
                ),
                const SizedBox(width: 10),
              ],
            ),
          ],
        ),
        body: Stack(
          children: [
            if (!_cameraStopped &&
                _cameraController != null &&
                _cameraController!.value.isInitialized)
              SizedBox(
                width: screenWidth,
                height: screenHeight,
                child: AspectRatio(
                  aspectRatio: _cameraController!.value.aspectRatio,
                  child: CameraPreview(_cameraController!),
                ),
              )
            else
              const Center(child: CircularProgressIndicator()),
            if (_showInstructionCard)
              Center(
                child: Card(
                  color: Colors.blue,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Text(
                      currentInstruction,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
              ),
          ],
        ));
  }
}
