import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:sipreti/pages/attendance_page.dart';
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
  late FaceDetector _faceDetector;

  @override
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
  }

  Future<void> _initializeCamera() async {
    cameras = await availableCameras();
    if (cameras != null && cameras!.isNotEmpty) {
      _cameraController = CameraController(
        cameras![1],
        ResolutionPreset.high,
      );

      await _cameraController!.initialize();
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
    if (_cameraController != null && _cameraController!.value.isInitialized) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => const Center(child: CircularProgressIndicator()),
      );
      try {
        final image = await _cameraController!.takePicture();
        
        // Mendapatkan path file
        final filePath = image.path;
        final file = File(filePath);

        // Cek ekstensi file (format: jpg, png, dll)
        final fileExtension =
            filePath.split('.').last.toUpperCase(); // Misalnya: JPG, PNG

        // Dapatkan ukuran file dalam byte
        final fileSizeBytes = await file.length();
        final fileSizeMB = fileSizeBytes / (1024 * 1024); // Konversi ke MB

        debugPrint("File type: $fileExtension");
        debugPrint("File size: ${fileSizeMB.toStringAsFixed(2)} MB");

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
    final inputImage = InputImage.fromFilePath(image.path);

    final detectionTime = Stopwatch()..start();
    final faces = await _faceDetector.processImage(inputImage);
    detectionTime.stop();
    debugPrint(
        'Time of Face detection: ${detectionTime.elapsedMilliseconds} ms');

    if (faces.isNotEmpty) {
      final face = faces.first;

      final croppedFace = await _cropFace(image.path, face.boundingBox);
      final embeddings = await _getFaceEmbeddings(croppedFace);

      var pegawaiBox = Hive.box('pegawai');
      List<dynamic> faceEmbeddings = pegawaiBox.get('face_embeddings');
      List<double> distances = [];

      for (int i = 0; i < faceEmbeddings.length; i++) {
        List<double> storedEmbedding = List<double>.from(faceEmbeddings[i]);
        double distance = manhattanDistance(embeddings, storedEmbedding);
        distances.add(distance);
      }
      const double threshold = 7;
      bool verifikasi = distances.any((d) => d < threshold);
      String message =
          verifikasi ? "Wajah terverifikasi" : "Wajah tidak terverifikasi";
      int value = verifikasi ? 1 : 0;
      debugPrint('Jarak Kedekatan: $distances');
      debugPrint('message: $message');
      debugPrint('Value: $value');
      final presensiBox = await Hive.openBox('presensi');
      await presensiBox.put('face_status', value);

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
    }
  }

  Future<img.Image> _cropFace(String imagePath, Rect boundingBox) async {
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
    final cropped = img.copyCrop(originalImage, left, top, width, height);

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
          Positioned(
            bottom: 50,
            left: screenWidth / 2 - 30,
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
      ),
    );
  }
}
