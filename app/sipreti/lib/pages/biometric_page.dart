import 'dart:io';
import 'dart:math';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:tflite_flutter/tflite_flutter.dart';
import 'package:image/image.dart' as img;
import 'package:hive_flutter/hive_flutter.dart';
import 'package:sipreti/services/api_service.dart';

class BiometricPage extends StatefulWidget {
  const BiometricPage({super.key});

  @override
  State<BiometricPage> createState() => _BiometricPageState();
}

class _BiometricPageState extends State<BiometricPage> {
  CameraController? _cameraController;
  List<CameraDescription>? cameras;
  XFile? capturedImage;
  File? _croppedFace;
  Interpreter? _interpreter;
  final ApiService _apiService = ApiService();

  bool _showInstructionCard = true;
  bool _cameraButtonEnabled = false;
  late FaceDetector _faceDetector;
  bool _isProcessing = false;

  final List<String> instructions = [
    "Kedipkan mata",
    "Tersenyum ke Kamera",
    "Menoleh ke Kiri",
    "Menoleh ke Kanan"
  ];

  late String currentInstruction;

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
        performanceMode: FaceDetectorMode.fast,
      ),
    );
    _selectRandomInstruction();
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
      _isProcessing = false;
    }
  }

  void _onInstructionCompleted() async {
    setState(() {
      _showInstructionCard = false;
    });

    await Future.delayed(const Duration(seconds: 1));
    _cameraController?.stopImageStream();

    setState(() {
      _cameraButtonEnabled = true;
    });
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

  double euclideanDistance(List<double> e1, List<double> e2) {
    double sum = 0.0;
    for (int i = 0; i < e1.length; i++) {
      sum += pow((e1[i] - e2[i]), 2);
    }
    return sqrt(sum);
  }

  Future<void> _captureImage() async {
    if (_cameraController != null && _cameraController!.value.isInitialized) {
      try {
        final image = await _cameraController!.takePicture();
        setState(() {
          capturedImage = image;
        });

        await _detectAndCropFace(image);
      } catch (e) {
        debugPrint("Error capturing image: $e");
      }
    }
  }

  Future<void> _detectAndCropFace(XFile image) async {
    final faceDetector = FaceDetector(
      options: FaceDetectorOptions(
        enableContours: true,
        enableClassification: true,
        performanceMode: FaceDetectorMode.accurate,
      ),
    );

    final inputImage = InputImage.fromFilePath(image.path);
    final faces = await faceDetector.processImage(inputImage);

    if (faces.isNotEmpty) {
      final face = faces.first;
      final croppedFace = await _cropFace(image.path, face.boundingBox);
      setState(() {
        _croppedFace = croppedFace;
      });

      final Stopwatch extractionTime = Stopwatch()..start();

      final embeddings = await _getFaceEmbeddings(_croppedFace!);
      extractionTime.stop();
      debugPrint(
          'Time taken for Extraction: ${extractionTime.elapsedMicroseconds} µs');
      debugPrint(embeddings.toString());

      var pegawaiBox = Hive.box('pegawai');

      String idPegawai = pegawaiBox.get('id_pegawai');
      List<dynamic> faceEmbeddings = pegawaiBox.get('face_embeddings');

      debugPrint(faceEmbeddings.toString());

      final Stopwatch localCalculation = Stopwatch()..start();
      List<double> distances = [];

      for (int i = 0; i < faceEmbeddings.length; i++) {
        List<double> storedEmbedding = List<double>.from(faceEmbeddings[i]);
        double distance = euclideanDistance(embeddings, storedEmbedding);
        distances.add(distance);
      }

      localCalculation.stop();
      debugPrint('Local Euclidean Distance: $euclideanDistance');
      debugPrint(
          'Time taken for Local Euclidean Distance: ${localCalculation.elapsedMicroseconds} µs');

      debugPrint('Semua jarak kedekatan: $distances');

      // Cek apakah ada jarak di bawah threshold (misal 1.0)
      const double threshold = 1.0;
      bool verifikasi = distances.any((d) => d < threshold);

      String message =
          verifikasi ? "Wajah terverifikasi" : "Wajah tidak terverifikasi";
      int value = verifikasi ? 1 : 0;

      // Tampilkan hasil akhir
      debugPrint('Jarak Kedekatan: $distances');
      debugPrint('message: $message');
      debugPrint('Value: $value');

      final Stopwatch cloudCalculation = Stopwatch()..start();

      verifyFace(idPegawai.toString(), embeddings);

      debugPrint('Cloud Euclidean Distance: $cloudCalculation');
      debugPrint(
          'Time taken for Cloud Euclidean Distance: ${cloudCalculation.elapsedMicroseconds} µs');

      _showCapturedImageDialog(showError: false);

      Future.delayed(const Duration(seconds: 3), () {
        if (mounted) {
          Navigator.pushReplacementNamed(context, '/attendance');
        }
      });
    } else {
      _showCapturedImageDialog(showError: true);
    }

    faceDetector.close();
  }

  Future<File> _cropFace(String imagePath, Rect boundingBox) async {
    final originalImage = img.decodeImage(File(imagePath).readAsBytesSync());

    if (originalImage == null) {
      throw Exception("Error reading the original image");
    }

    final int left = boundingBox.left.toInt().clamp(0, originalImage.width);
    final int top = boundingBox.top.toInt().clamp(0, originalImage.height);
    final int width =
        boundingBox.width.toInt().clamp(0, originalImage.width - left);
    final int height =
        boundingBox.height.toInt().clamp(0, originalImage.height - top);

    final croppedImage = img.copyCrop(originalImage, left, top, width, height);

    final croppedFaceFile = File('${imagePath}_cropped.png')
      ..writeAsBytesSync(img.encodePng(croppedImage));
    return croppedFaceFile;
  }

  Future<List<double>> _getFaceEmbeddings(File faceImage) async {
    Float32List input = await _loadAndNormalizeImage(faceImage);

    var reshapedInput = input.buffer.asFloat32List().reshape([1, 112, 112, 3]);

    var output =
        List<List<double>>.generate(1, (_) => List<double>.filled(192, 0.0));

    _interpreter?.run(reshapedInput, output);

    // return output[0];

    List<double> roundedOutput =
        output[0].map((e) => double.parse(e.toStringAsFixed(8))).toList();

    return roundedOutput;
  }

  Future<Float32List> _loadAndNormalizeImage(File faceImage) async {
    final image = img.decodeImage(faceImage.readAsBytesSync())!;

    final resizedImage = img.copyResize(image, width: 112, height: 112);

    Float32List input = Float32List(112 * 112 * 3);
    int index = 0;
    for (int y = 0; y < 112; y++) {
      for (int x = 0; x < 112; x++) {
        final pixel = resizedImage.getPixel(x, y);
        final r = img.getRed(pixel);
        final g = img.getGreen(pixel);
        final b = img.getBlue(pixel);

        input[index++] = r.toDouble() / 255.0;
        input[index++] = g.toDouble() / 255.0;
        input[index++] = b.toDouble() / 255.0;
      }
    }

    return input;
  }

  void _showCapturedImageDialog({required bool showError}) {
    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (BuildContext context) {
        return Dialog(
          backgroundColor: showError
              ? Colors.red.withOpacity(0.5)
              : Colors.green.withOpacity(0.5),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                if (showError)
                  const Text(
                    "No face detected, please try again.",
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                    ),
                    textAlign: TextAlign.center,
                  )
                else if (_croppedFace != null)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(6),
                    child: SizedBox(
                      width: 200,
                      height: 200,
                      child: Image.file(
                        _croppedFace!,
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  void dispose() {
    _cameraController?.dispose();
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
                const CircleAvatar(
                  backgroundImage:
                      AssetImage('assets/images/default_profile.png'),
                  radius: 16,
                ),
                const SizedBox(width: 10),
              ],
            ),
          ],
        ),
        body: Stack(
          children: [
            if (_cameraController != null &&
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
            if (_cameraButtonEnabled)
              Positioned(
                bottom: 50,
                left: screenWidth / 2 - 40,
                child: GestureDetector(
                  onTap: _captureImage,
                  child: Image.asset(
                    'assets/images/camera_button.png',
                    width: 80,
                    height: 80,
                  ),
                ),
              ),
          ],
        ));
  }
}
