import 'package:flutter/material.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:intl/intl.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:camera/camera.dart';
import 'package:sipreti/pages/document_page.dart';
import 'dart:io';
import 'dart:convert';
import 'package:sipreti/services/api_service.dart';
import 'package:sipreti/utils/dialog.dart';
import 'dart:typed_data';
import 'package:image/image.dart' as img;

class AttendancePage extends StatefulWidget {
  final XFile? capturedImage;

  const AttendancePage({super.key, required this.capturedImage});

  @override
  State<AttendancePage> createState() => _AttendancePageState();
}

class _AttendancePageState extends State<AttendancePage> {
  late String namaLokasi = '';
  double? latitude;
  double? longitude;
  late DateTime waktuAbsensi = DateTime.now();
  String? jamAbsensi;
  int? checkMode;
  int? jenisAbsensi;
  int? faceStatus;
  late String tanggal = '';
  late String hari = '';
  late String lamaVerifikasi = '';
  String distances = '';
  File? supportingDocument;
  final ApiService apiService = ApiService();

  bool includePhoto = true;

  String? urlFoto;
  final String baseUrl = 'http://35.187.225.70/sipreti/uploads/foto_pegawai/';

  @override
  void initState() {
    super.initState();
    _loadPresensiData();

    var pegawaiBox = Hive.box('pegawai');
    setState(() {
      urlFoto = pegawaiBox.get('url_foto');
    });
  }

  Future<File?> resizeImageFile(File originalFile, double scaleFactor) async {
    // Baca file sebagai byte
    final bytes = await originalFile.readAsBytes();

    // Decode menjadi image
    final image = img.decodeImage(bytes);
    if (image == null) return null;

    // Hitung ukuran baru
    final newWidth = (image.width * scaleFactor).round();
    final newHeight = (image.height * scaleFactor).round();

    // Resize
    final resized = img.copyResize(image, width: newWidth, height: newHeight);

    // Encode ke PNG (bisa juga JPG jika perlu)
    final resizedBytes = Uint8List.fromList(img.encodeJpg(resized));

    // Simpan ke file sementara
    final tempDir = Directory.systemTemp;
    final resizedFile = await File('${tempDir.path}/resized_image.jpg')
        .writeAsBytes(resizedBytes);

    return resizedFile;
  }

  void submitAttendance() async {
    showLoadingDialog(context);

    final DateTime now = DateTime.now();
    final DateTime absensiTime = DateTime.parse(waktuAbsensi.toString());

    final Duration difference = now.difference(absensiTime).abs();

    if (difference > const Duration(minutes: 5)) {
      Navigator.pop(context);
      showErrorDialog(
          context, 'Waktu absensi tidak valid (lebih dari 5 menit)');
      Navigator.pushReplacementNamed(context, '/');
      return;
    }

    var pegawaiBox = Hive.box('pegawai');

    String idPegawai = pegawaiBox.get('id_pegawai');

    final File? fotoFile = includePhoto && widget.capturedImage != null
        ? await resizeImageFile(File(widget.capturedImage!.path), 0.25)
        : null;

    final submitTime = Stopwatch()..start();

    try {
      final result = await apiService.storeAttendance(
          jenisAbsensi: jenisAbsensi!,
          idPegawai: int.parse(idPegawai),
          checkMode: checkMode!,
          waktuAbsensi: waktuAbsensi.toString(),
          latitude: latitude!,
          longitude: longitude!,
          namaLokasi: namaLokasi,
          lamaAbsensi: lamaVerifikasi,
          jarakVektor: distances,
          fotoPresensi: fotoFile,
          dokumen: supportingDocument);

      submitTime.stop();
      final submissionTime =
          '${(submitTime.elapsedMilliseconds / 1000).toStringAsFixed(3)} s';

      final lamaAbsensiBaru =
          'Lama Verifikasi Wajah: $lamaVerifikasi, Lama Pengiriman Presensi: $submissionTime';

      if (result['status'] == 200 && result['data'] != null) {
        final int idLogAbsensi =
            int.parse(result['data']['id_log_absensi'].toString());
        updateAttendance(idLogAbsensi, lamaAbsensiBaru);
      } else {
        if (mounted) {
          showErrorDialog(
              context, 'Gagal menyimpan update absensi: ${result['message']}');
        }
      }

      if (mounted) Navigator.pop(context);

      final dashboardBox = await Hive.openBox('dashboard');
      if (checkMode == 0) {
        await dashboardBox.put('checkin', jamAbsensi);
      } else if (checkMode == 1) {
        await dashboardBox.put('checkout', jamAbsensi);
      } else {
        debugPrint("Invalid check mode: $checkMode");
      }

      final tanggalPresensi = DateFormat('yyyy-MM-dd').format(waktuAbsensi);
      await dashboardBox.put('tanggal_presensi', tanggalPresensi);

      final presensiBox = await Hive.openBox('presensi');
      await presensiBox.clear();

      if (mounted) {
        if (result['error'] == true) {
          final String message = extractMessage(result["message"]);
          await showErrorDialog(context, message);
          return;
        } else {
          showSuccessDialog(context, 'Berhasil Presensi');
          Future.delayed(const Duration(seconds: 2), () {
            Navigator.pushNamed(context, '/');
          });
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context);
        showErrorDialog(context, 'Terjadi kesalahan: $e');
      }
    }
  }

  void updateAttendance(int idLogAbsensi, String lamaAbsensiBaru) async {
    var pegawaiBox = Hive.box('pegawai');

    String idPegawai = pegawaiBox.get('id_pegawai');

    final File? fotoFile = includePhoto && widget.capturedImage != null
        ? File(widget.capturedImage!.path)
        : null;

    try {
      final result = await apiService.updateAttendance(
          idLogAbsensi: idLogAbsensi,
          jenisAbsensi: jenisAbsensi!,
          idPegawai: int.parse(idPegawai),
          checkMode: checkMode!,
          waktuAbsensi: waktuAbsensi.toString(),
          latitude: latitude!,
          longitude: longitude!,
          namaLokasi: namaLokasi,
          lamaAbsensi: lamaAbsensiBaru,
          jarakVektor: distances,
          fotoPresensi: fotoFile,
          dokumen: supportingDocument);

      if (mounted) {
        if (result['error'] == true) {
          final String message = extractMessage(result["message"]);
          await showErrorDialog(context, message);
          return;
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context);
        showErrorDialog(context, 'Terjadi kesalahan: $e');
      }
    }
  }

  String extractMessage(String rawMessage) {
    try {
      final jsonPart = rawMessage.split('-').last.trim();
      final decoded = json.decode(jsonPart);
      return decoded['message'] ?? 'Terjadi kesalahan';
    } catch (e) {
      return 'Terjadi kesalahan';
    }
  }

  Future<void> _loadPresensiData() async {
    final presensiBox = await Hive.openBox('presensi');
    await initializeDateFormatting('id_ID', null);

    setState(() {
      namaLokasi =
          presensiBox.get('nama_lokasi', defaultValue: 'Tidak diketahui');
      latitude = presensiBox.get('lattitude', defaultValue: 0.0);
      longitude = presensiBox.get('longitude', defaultValue: 0.0);
      waktuAbsensi =
          presensiBox.get('waktu_absensi', defaultValue: DateTime.now());
      jamAbsensi = DateFormat('HH:mm').format(waktuAbsensi);
      checkMode = presensiBox.get('check_mode', defaultValue: 0);
      jenisAbsensi = presensiBox.get('jenis_absensi', defaultValue: 0);
      faceStatus = presensiBox.get('face_status', defaultValue: 0);
      List<double> tempDistances =
          List<double>.from(presensiBox.get('distances', defaultValue: []));
      String distancesString = tempDistances.join(', ');
      distances = distancesString;
      tanggal = DateFormat('d MMMM y', 'id_ID').format(waktuAbsensi);
      hari = DateFormat('EEEE', 'id_ID').format(waktuAbsensi);
      lamaVerifikasi =
          presensiBox.get('verification_time', defaultValue: 'Tidak diketahui');
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.blue,
        title: Text(
          checkMode == 0 ? "Check In" : "Check Out",
          style: const TextStyle(
              fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () {
            Navigator.pop(context);
          },
        ),
        actions: [
          Image.asset(
            'assets/images/pemkot_malang_logo.png',
            height: 40,
          ),
          const SizedBox(width: 8),
          CircleAvatar(
            backgroundImage: urlFoto != null
                ? NetworkImage(baseUrl + urlFoto!)
                : const AssetImage('assets/images/default_profile.png')
                    as ImageProvider,
          ),
          const SizedBox(width: 10),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Container(
              width: double.infinity,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                boxShadow: const [
                  BoxShadow(color: Colors.black12, blurRadius: 5),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (includePhoto)
                    ClipRRect(
                      borderRadius: const BorderRadius.only(
                        topLeft: Radius.circular(10),
                        topRight: Radius.circular(10),
                      ),
                      child: widget.capturedImage != null
                          ? Image.file(
                              File(widget.capturedImage!.path),
                              width: double.infinity,
                              height: 200,
                              fit: BoxFit.cover,
                            )
                          : Image.asset(
                              'assets/images/default_profile.png',
                              width: double.infinity,
                              height: 200,
                              fit: BoxFit.cover,
                            ),
                    ),
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              "Kirim data dengan foto?",
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                            Row(
                              children: [
                                Text(
                                  includePhoto ? 'Ya' : 'Tidak',
                                  style: TextStyle(
                                    color: includePhoto
                                        ? Colors.green
                                        : Colors.red,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Switch(
                                  value: includePhoto,
                                  onChanged: (value) {
                                    setState(() {
                                      includePhoto = value;
                                    });
                                  },
                                  activeColor: Colors.green,
                                  inactiveThumbColor: Colors.red,
                                  inactiveTrackColor: Colors.red[200],
                                ),
                              ],
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            const Icon(Icons.bookmark, color: Colors.black),
                            const SizedBox(width: 10),
                            Text(
                              jenisAbsensi == 0
                                  ? "Absensi Reguler"
                                  : "Absensi Dinas",
                              style: const TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            const Icon(Icons.calendar_today,
                                color: Colors.black),
                            const SizedBox(width: 10),
                            Text(
                              "$hari, $tanggal",
                              style: const TextStyle(fontSize: 16),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            const Icon(Icons.access_time, color: Colors.black),
                            const SizedBox(width: 10),
                            Text(
                              "$jamAbsensi WIB",
                              style: const TextStyle(fontSize: 16),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            const Icon(Icons.location_on, color: Colors.black),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                namaLokasi,
                                style: const TextStyle(fontSize: 14),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            const Icon(Icons.timelapse, color: Colors.black),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                'Lama Verifikasi Wajah: $lamaVerifikasi',
                                style: const TextStyle(fontSize: 14),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            const Icon(Icons.display_settings,
                                color: Colors.black),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                'Jarak Kedekatan: $distances',
                                style: const TextStyle(fontSize: 14),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            Icon(
                              faceStatus == 1
                                  ? Icons.emoji_emotions
                                  : Icons.mood_bad,
                              color:
                                  faceStatus == 1 ? Colors.green : Colors.red,
                            ),
                            const SizedBox(width: 10),
                            Text(
                              faceStatus == 1
                                  ? "Wajah Terverifikasi"
                                  : "Wajah Tidak Terverifikasi",
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color:
                                    faceStatus == 1 ? Colors.green : Colors.red,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: const BoxDecoration(
                      borderRadius: BorderRadius.only(
                        bottomLeft: Radius.circular(10),
                        bottomRight: Radius.circular(10),
                      ),
                    ),
                    child: Column(
                      children: [
                        SizedBox(
                          width: double.infinity,
                          child: OutlinedButton(
                            onPressed: () {
                              Navigator.pushNamed(context, '/biometric');
                            },
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              side: const BorderSide(color: Colors.blue),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(6),
                              ),
                            ),
                            child: const Text(
                              "FOTO ULANG",
                              style: TextStyle(
                                  fontSize: 16,
                                  color: Colors.blue,
                                  fontWeight: FontWeight.bold),
                            ),
                          ),
                        ),
                        Column(
                          children: [
                            if (jenisAbsensi == 1) const SizedBox(height: 10),
                            if (jenisAbsensi == 1)
                              SizedBox(
                                width: double.infinity,
                                child: OutlinedButton(
                                  onPressed: () async {
                                    final result = await Navigator.push<File>(
                                      context,
                                      MaterialPageRoute(
                                          builder: (_) => const DocumentPage()),
                                    );

                                    if (result != null) {
                                      setState(() {
                                        supportingDocument = result;
                                      });
                                    }
                                  },
                                  style: OutlinedButton.styleFrom(
                                    padding: const EdgeInsets.symmetric(
                                        vertical: 12),
                                    side: const BorderSide(color: Colors.green),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                  ),
                                  child: const Text(
                                    "DOKUMEN PENDUKUNG",
                                    style: TextStyle(
                                        fontSize: 16,
                                        color: Colors.green,
                                        fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ),
                          ],
                        ),
                        (faceStatus == 1 &&
                                ((jenisAbsensi == 0) ||
                                    (jenisAbsensi == 1 &&
                                        supportingDocument != null)))
                            ? Column(
                                children: [
                                  const SizedBox(height: 10),
                                  SizedBox(
                                    width: double.infinity,
                                    child: ElevatedButton(
                                      onPressed: submitAttendance,
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.blue,
                                        padding: const EdgeInsets.symmetric(
                                            vertical: 12),
                                        shape: RoundedRectangleBorder(
                                          borderRadius:
                                              BorderRadius.circular(6),
                                        ),
                                      ),
                                      child: Text(
                                        checkMode == 0
                                            ? "SELESAI CHECK IN"
                                            : "SELESAI CHECK OUT",
                                        style: const TextStyle(
                                            fontSize: 16,
                                            color: Colors.white,
                                            fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                  ),
                                ],
                              )
                            : const SizedBox.shrink(),
                        const SizedBox(height: 10),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed: () {
                              Navigator.pushNamed(context, '/');
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.red,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(6),
                              ),
                            ),
                            child: Text(
                              checkMode == 0
                                  ? "BATAL CHECK IN"
                                  : "BATAL CHECK OUT",
                              style: const TextStyle(
                                  fontSize: 16,
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            )
          ],
        ),
      ),
    );
  }
}
