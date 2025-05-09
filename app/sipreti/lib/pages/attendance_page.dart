import 'package:flutter/material.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:intl/intl.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:camera/camera.dart';
import 'dart:io';
import 'package:sipreti/services/api_service.dart';

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
  final int jenisAbsensi = 0;
  int? faceStatus;
  late String tanggal = '';
  late String hari = '';
  final ApiService apiService = ApiService();

  @override
  void initState() {
    super.initState();
    _loadPresensiData();
  }

  void submitAttendance() async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator()),
    );

    final DateTime now = DateTime.now();
    final DateTime absensiTime = DateTime.parse(waktuAbsensi.toString());

    final Duration difference = now.difference(absensiTime).abs();

    if (difference > const Duration(minutes: 5)) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
            content: Text('Waktu absensi tidak valid (lebih dari 5 menit)')),
      );
      Navigator.pushReplacementNamed(context, '/');
      return;
    }

    var pegawaiBox = Hive.box('pegawai');

    String idPegawai = pegawaiBox.get('id_pegawai');

    final File fotoFile = File(widget.capturedImage!.path);

    try {
      final result = await apiService.storeAttendance(
        jenisAbsensi: 0,
        idPegawai: int.parse(idPegawai),
        checkMode: checkMode!,
        waktuAbsensi: waktuAbsensi.toString(),
        latitude: latitude!,
        longitude: longitude!,
        namaLokasi: namaLokasi,
        namaKamera: "Kamera Depan",
        fotoPresensi: fotoFile,
      );

      if (mounted) Navigator.pop(context);
      final presensiBox = await Hive.openBox('presensi');
      await presensiBox.clear();

      if (mounted) {
        if (result['error'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(result['message'] ?? 'Presensi gagal')),
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Presensi berhasil!')),
          );
          Navigator.pushNamed(context, '/');
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Terjadi kesalahan: $e')),
        );
      }
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
      faceStatus = presensiBox.get('face_status', defaultValue: 0);
      tanggal = DateFormat('d MMMM y', 'id_ID').format(waktuAbsensi);
      hari = DateFormat('EEEE', 'id_ID').format(waktuAbsensi);
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
          const CircleAvatar(
            backgroundImage: AssetImage('assets/images/default_profile.png'),
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
                                  ? "Data Valid"
                                  : "Data Tidak Valid",
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
                              style:
                                  TextStyle(fontSize: 16, color: Colors.blue),
                            ),
                          ),
                        ),
                        faceStatus == 1
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
                                            fontSize: 16, color: Colors.white),
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
                                  fontSize: 16, color: Colors.white),
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
