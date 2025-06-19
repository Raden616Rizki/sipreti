import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:sipreti/pages/attendance_page.dart';
import 'package:tflite_flutter/tflite_flutter.dart';
import 'package:hive/hive.dart';
import 'package:sipreti/utils/dialog.dart';
import 'dart:math';
import 'package:image/image.dart' as img;
import 'package:flutter/foundation.dart';

class UploadBiometricPage extends StatefulWidget {
  const UploadBiometricPage({super.key});

  @override
  State<UploadBiometricPage> createState() => _UploadBiometricPageState();
}

class _UploadBiometricPageState extends State<UploadBiometricPage> {
  XFile? _selectedImage;
  String _status = 'Silakan unggah foto Anda.';
  late FaceDetector _faceDetector;
  bool _isProcessing = false;

  Interpreter? _interpreter;

  String? urlFoto;
  final String baseUrl = 'http://35.187.225.70/sipreti/uploads/foto_pegawai/';

  @override
  void initState() {
    super.initState();
    _loadModel();
    _faceDetector = FaceDetector(
      options: FaceDetectorOptions(
        enableClassification: true,
        enableLandmarks: true,
        enableContours: true,
        performanceMode: FaceDetectorMode.accurate,
      ),
    );
    var pegawaiBox = Hive.box('pegawai');
    setState(() {
      urlFoto = pegawaiBox.get('url_foto');
    });
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

  @override
  void dispose() {
    _faceDetector.close();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final pickedFile = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 100,
    );

    if (pickedFile != null) {
      setState(() {
        _selectedImage = pickedFile;
        _status = 'Memproses foto...';
        _isProcessing = true;
      });

      await _detectAndCropFace(pickedFile);
    }
  }

  Future<void> _detectAndCropFace(XFile image) async {
    final inputImage = InputImage.fromFilePath(image.path);
    final faces = await _faceDetector.processImage(inputImage);

    if (faces.isEmpty) {
      setState(() {
        _status = 'Tidak ada wajah terdeteksi. Coba ulangi.';
        _isProcessing = false;
      });
      return;
    }

    final face = faces.first;
    final croppedFace = await _cropFace(image, face.boundingBox);
    final embeddings = await _getFaceEmbeddings(croppedFace);

    final pegawaiBox = Hive.box('pegawai');
    List<dynamic> faceEmbeddings =
        pegawaiBox.get('face_embeddings', defaultValue: []);

    if (faceEmbeddings.isEmpty) {
      setState(() {
        _status = 'Data wajah tidak ditemukan di penyimpanan.';
        _isProcessing = false;
      });
      return;
    }

    List<double> distances = [];
    for (var stored in faceEmbeddings) {
      List<double> storedEmbedding = List<double>.from(stored);
      double d = euclideanDistance(embeddings, storedEmbedding);
      distances.add(double.parse(d.toStringAsFixed(4)));
    }

    const double threshold = 0.9;
    final verified = distances.any((d) => d < threshold);

    final presensiBox = await Hive.openBox('presensi');
    await presensiBox.put('face_status', verified ? 1 : 0);
    await presensiBox.put('distances', distances);
    await presensiBox.put(
        'verification_time', DateTime.now().toIso8601String());

    setState(() {
      _status = verified ? 'Wajah terverifikasi' : 'Wajah tidak cocok';
      _isProcessing = false;
    });

    if (mounted) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (context) => AttendancePage(
            capturedImage: _selectedImage,
          ),
        ),
      );
    }
  }

  Future<img.Image> _cropFace(XFile image, Rect boundingBox) async {
    final bytes = await image.readAsBytes();
    final original = img.decodeImage(bytes);
    if (original == null) throw Exception("Gagal membaca gambar");

    const widthReductionRatio = 0.3;
    const heightReductionRatio = 0.1;

    final deltaWidth = boundingBox.width * widthReductionRatio;
    final deltaHeight = boundingBox.height * heightReductionRatio;

    final newLeft =
        (boundingBox.left + deltaWidth / 2).clamp(0, original.width.toDouble());
    final newTop = (boundingBox.top + deltaHeight / 2)
        .clamp(0, original.height.toDouble());
    final newWidth =
        (boundingBox.width - deltaWidth).clamp(0, original.width - newLeft);
    final newHeight =
        (boundingBox.height - deltaHeight).clamp(0, original.height - newTop);

    return img.copyCrop(
      original,
      newLeft.toInt(),
      newTop.toInt(),
      newWidth.toInt(),
      newHeight.toInt(),
    );
  }

  Future<List<double>> _getFaceEmbeddings(img.Image faceImage) async {
    Float32List input = await _loadAndNormalizeImage(faceImage);
    var reshapedInput = input.buffer.asFloat32List().reshape([1, 112, 112, 3]);

    var output = List<List<double>>.generate(1, (_) => List.filled(192, 0.0));
    _interpreter?.run(reshapedInput, output);
    return output[0];
  }

  Future<Float32List> _loadAndNormalizeImage(img.Image image) async {
    final resized = img.copyResize(image, width: 112, height: 112);
    final pixels = resized.getBytes(format: img.Format.rgb);
    final Float32List floatPixels = Float32List(112 * 112 * 3);

    for (int i = 0; i < pixels.length; i++) {
      floatPixels[i] = pixels[i] / 255.0;
    }

    return floatPixels;
  }

  double euclideanDistance(List<double> e1, List<double> e2) {
    double sum = 0.0;
    for (int i = 0; i < e1.length; i++) {
      sum += pow((e1[i] - e2[i]), 2);
    }
    return sqrt(sum);
  }

  @override
  Widget build(BuildContext context) {
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
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            if (_selectedImage != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.file(File(_selectedImage!.path), height: 240),
              )
            else
              Container(
                height: 240,
                width: double.infinity,
                color: Colors.grey[300],
                alignment: Alignment.center,
                child: const Text('Belum ada foto yang dipilih'),
              ),
            const SizedBox(height: 20),
            Text(
              _status,
              style: const TextStyle(fontSize: 16),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: _isProcessing ? null : _pickImage,
              icon: const Icon(Icons.upload),
              label: const Text('Unggah Foto'),
            ),
          ],
        ),
      ),
    );
  }
}
