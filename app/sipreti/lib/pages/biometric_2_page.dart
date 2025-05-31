import 'dart:math';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:sipreti/pages/attendance_page.dart';
import 'package:sipreti/utils/dialog.dart';
import 'package:tflite_flutter/tflite_flutter.dart';
import 'package:image/image.dart' as img;
import 'package:hive_flutter/hive_flutter.dart';

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

  bool _showInstructionCard = true;
  // bool _cameraButtonEnabled = false;
  late FaceDetector _faceDetector;
  bool _isProcessing = false;
  bool _cameraStopped = false;

  final List<String> instructions = [
    "Kedipkan mata",
    "Tersenyum ke Kamera",
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
        }
      }
    } catch (e) {
      if (mounted) {
        showErrorDialog(context, "Face processing error: $e");
      }
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
        enableAudio: false,
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
      if (mounted) {
        showErrorDialog(context, "Error loading model: $e");
      }
    }
  }

  double euclideanDistance(List<double> e1, List<double> e2) {
    double sum = 0.0;
    for (int i = 0; i < e1.length; i++) {
      sum += pow((e1[i] - e2[i]), 2);
    }
    return sqrt(sum);
  }

  double manhattanDistance(List<double> e1, List<double> e2) {
    double sum = 0.0;
    for (int i = 0; i < e1.length; i++) {
      sum += (e1[i] - e2[i]).abs();
    }
    return sum;
  }

  Future<img.Image> _cropFace(XFile image, Rect boundingBox) async {
    final bytes = await image.readAsBytes();
    final originalImage = img.decodeImage(bytes);
    if (originalImage == null) {
      throw Exception("Error reading the original image");
    }

    const double widthReductionRatio = 0.3;
    const double heightReductionRatio = 0.1;

    // Hitung pengurangan absolut
    final double deltaWidth = boundingBox.width * widthReductionRatio;
    final double deltaHeight = boundingBox.height * heightReductionRatio;

    // Hitung bounding box baru
    final double newLeft = (boundingBox.left + deltaWidth / 2)
        .clamp(0, originalImage.width.toDouble());
    final double newTop = (boundingBox.top + deltaHeight / 2)
        .clamp(0, originalImage.height.toDouble());

    final double newWidth = (boundingBox.width - deltaWidth)
        .clamp(0, originalImage.width - newLeft);
    final double newHeight = (boundingBox.height - deltaHeight)
        .clamp(0, originalImage.height - newTop);

    final cropped = img.copyCrop(
      originalImage,
      newLeft.toInt(),
      newTop.toInt(),
      newWidth.toInt(),
      newHeight.toInt(),
    );

    return cropped;
  }

  Future<List<double>> _getFaceEmbeddings(img.Image faceImage) async {
    Float32List input = await _loadAndNormalizeImage(faceImage);

    var reshapedInput = input.buffer.asFloat32List().reshape([1, 112, 112, 3]);

    var output =
        List<List<double>>.generate(1, (_) => List<double>.filled(192, 0.0));
    _interpreter?.run(reshapedInput, output);

    return output[0];
  }

  Future<Float32List> _loadAndNormalizeImage(img.Image image) async {
    final resizedImage = img.copyResize(image,
        width: 112, height: 112, interpolation: img.Interpolation.linear);

    final pixels = resizedImage.getBytes(format: img.Format.rgb);
    final Float32List floatPixels = Float32List(112 * 112 * 3);

    for (int i = 0; i < pixels.length; i++) {
      floatPixels[i] = pixels[i] / 255.0;
    }

    return floatPixels;
  }

  Future<void> _detectAndCropFace(
      XFile image, PreprocessingMethod method) async {
    final totalTime = Stopwatch()..start();

    final inputImage = InputImage.fromFilePath(image.path);
    final faces = await _faceDetector.processImage(inputImage);

    if (faces.isNotEmpty) {
      final face = faces.first;
      final croppedFace = await _cropFace(image, face.boundingBox);

      final processedFace = _applyPreprocessing(croppedFace, method);

      // Tampilkan hasil preprocessing sementara
      final pngBytes = Uint8List.fromList(img.encodePng(processedFace));

      if (mounted) {
        showDialog(
          context: context,
          builder: (context) {
            return Dialog(
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Image.memory(pngBytes),
              ),
            );
          },
        );
      }

      // Auto dismiss after 3 seconds
      await Future.delayed(const Duration(seconds: 3));
      if (mounted && Navigator.of(context).canPop()) {
        Navigator.of(context).pop();
      }

      final embeddings = await _getFaceEmbeddings(processedFace);
      var pegawaiBox = Hive.box('pegawai');

      List<dynamic> faceEmbeddings = pegawaiBox.get('face_embeddings');
      const double threshold = 0.9;
      bool verifikasi = false;

      List<double> distances = [];

      for (int i = 0; i < faceEmbeddings.length; i++) {
        List<double> storedEmbedding = List<double>.from(faceEmbeddings[i]);
        double distance = euclideanDistance(embeddings, storedEmbedding);
        distance = double.parse(distance.toStringAsFixed(4));
        distances.add(distance);
      }

      verifikasi = distances.any((d) => d < threshold);
      int value = verifikasi ? 1 : 0;

      final presensiBox = await Hive.openBox('presensi');
      await presensiBox.put('face_status', value);
      await presensiBox.put('distances', distances);

      totalTime.stop();
      final verificationTime =
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
      if (mounted) {
        Navigator.of(context).pop();
      }

      _showCapturedImageDialog();
      Future.delayed(const Duration(seconds: 3), () {
        if (mounted) {
          Navigator.pushNamed(context, '/biometric');
        }
      });
    }
  }

  Future<void> _captureImage() async {
    showLoadingDialog(context);

    if (_cameraController != null && _cameraController!.value.isInitialized) {
      try {
        final image = await _cameraController!.takePicture();
        setState(() {
          capturedImage = image;
          _cameraStopped = true;
        });

        await _detectAndCropFace(image, PreprocessingMethod.gamma);
      } catch (e) {
        if (mounted) {
          Navigator.of(context).pop();
          showErrorDialog(context, "Error capturing image: $e");
        }
      }
    }
  }

  img.Image _applyPreprocessing(img.Image image, PreprocessingMethod method) {
    switch (method) {
      case PreprocessingMethod.gamma:
        return _applyGammaCorrection(image, gamma: 0.8);
      case PreprocessingMethod.clahe:
        return _applyClahe(image);
      case PreprocessingMethod.gammaClahe:
        final gammaCorrected = _applyGammaCorrection(image, gamma: 0.8);
        return _applyClahe(gammaCorrected);
      case PreprocessingMethod.original:
      default:
        return image;
    }
  }

  img.Image _applyGammaCorrection(img.Image image, {double gamma = 1.0}) {
    final lut = List<int>.generate(256, (i) {
      return ((255.0 * pow(i / 255.0, 1.0 / gamma)).clamp(0.0, 255.0)).toInt();
    });

    for (int y = 0; y < image.height; y++) {
      for (int x = 0; x < image.width; x++) {
        final pixel = image.getPixel(x, y);
        final r = img.getRed(pixel);
        final g = img.getGreen(pixel);
        final b = img.getBlue(pixel);
        image.setPixelRgba(x, y, lut[r], lut[g], lut[b]);
      }
    }
    return image;
  }

  img.Image _applyClahe(img.Image image) {
    // Simulasi CLAHE sederhana: histogram equalization manual.
    // Implementasi asli CLAHE membutuhkan native OpenCV.
    final gray = img.grayscale(image);
    final hist = List<int>.filled(256, 0);

    for (final p in gray.getBytes()) {
      hist[p]++;
    }

    final cdf = List<int>.filled(256, 0);
    cdf[0] = hist[0];
    for (int i = 1; i < 256; i++) {
      cdf[i] = cdf[i - 1] + hist[i];
    }

    final cdfMin = cdf.firstWhere((x) => x > 0);
    final totalPixels = gray.width * gray.height;
    final lut = List<int>.generate(256, (i) {
      return (((cdf[i] - cdfMin) / (totalPixels - cdfMin)) * 255)
          .clamp(0, 255)
          .toInt();
    });

    for (int y = 0; y < image.height; y++) {
      for (int x = 0; x < image.width; x++) {
        final pixel = image.getPixel(x, y);
        final r = img.getRed(pixel);
        final g = img.getGreen(pixel);
        final b = img.getBlue(pixel);

        final grayValue = (0.299 * r + 0.587 * g + 0.114 * b).toInt();
        final eq = lut[grayValue];

        image.setPixelRgba(x, y, eq, eq, eq);
      }
    }
    return image;
  }

  void _showCapturedImageDialog() {
    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (BuildContext context) {
        return Dialog(
          backgroundColor: const Color(0xFFFD5765),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          child: const Padding(
            padding: EdgeInsets.all(16.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  "Wajah Tidak Terdeteksi, Mohon Ulangi",
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
              Positioned(
                top: kToolbarHeight + 16,
                left: 16,
                right: 16,
                child: Card(
                  color: Colors.blue,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
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

enum PreprocessingMethod {
  original,
  gamma,
  clahe,
  gammaClahe,
}
