import 'package:flutter/material.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:intl/intl.dart';
import 'package:sipreti/services/api_service.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:sipreti/utils/dialog.dart';
import 'dart:convert';
import 'package:flutter/services.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  DashboardPageState createState() => DashboardPageState();
}

class DashboardPageState extends State<DashboardPage> {
  String? username;
  String? email;
  String? namaPegawai;
  String? nip;
  String? namaJabatan;
  String? urlFoto;
  final String baseUrl = 'http://35.187.225.70/sipreti/uploads/foto_pegawai/';
  final ApiService _apiService = ApiService();

  String checkin = '-';
  String checkout = '-';

  @override
  void initState() {
    super.initState();

    var box = Hive.box('userAndroid');
    var pegawaiBox = Hive.box('pegawai');
    // var testBox = Hive.box('test');

    setState(() {
      username = box.get('username');
      email = box.get('email');

      namaPegawai = pegawaiBox.get('nama');
      nip = pegawaiBox.get('nip');
      namaJabatan = pegawaiBox.get('nama_jabatan');
      urlFoto = pegawaiBox.get('url_foto');

      // kameraDepan = testBox.get('kameraDepan', defaultValue: false);
      // spamAbsensi = testBox.get('spamAbsensi', defaultValue: false);
    });
    initializeDateFormatting('id_ID', null);
    _loadTodayAttendance();
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

  Future<void> updatePegawaiData() async {
    showLoadingDialog(context);

    var pegawaiBox = Hive.box('pegawai');
    String idPegawai = pegawaiBox.get('id_pegawai');

    // Ambil model dari Hive
    final modelName = await Hive.box('settings')
        .get(hiveModelKey, defaultValue: FaceModelType.facenet.name);
    FaceModelType selectedModel = FaceModelType.values.firstWhere(
      (e) => e.name == modelName,
      orElse: () => FaceModelType.facenet,
    );

    // Kosongkan box pegawai lama
    await pegawaiBox.clear();

    // Ambil data sesuai model
    Map<String, dynamic> dataPegawai;
    if (selectedModel == FaceModelType.facenet) {
      dataPegawai = await _apiService.getPegawaiFacenet(idPegawai);
    } else if (selectedModel == FaceModelType.ghostfacenet) {
      dataPegawai = await _apiService.getPegawaiGhostfacenet(idPegawai);
    } else {
      dataPegawai = await _apiService.getPegawai(idPegawai);
    }

    if (mounted) {
      if (dataPegawai["error"] == true) {
        Navigator.of(context).pop();
        final String message = extractMessage(dataPegawai["message"]);
        await showErrorDialog(context, message);
        return;
      } else {
        await pegawaiBox.put('id_pegawai', dataPegawai['id_pegawai']);
        await pegawaiBox.put('nip', dataPegawai['nip']);
        await pegawaiBox.put('nama', dataPegawai['nama']);
        await pegawaiBox.put('url_foto', dataPegawai['url_foto']);
        await pegawaiBox.put('nama_jabatan', dataPegawai['nama_jabatan']);
        await pegawaiBox.put('nama_unit_kerja', dataPegawai['nama_unit_kerja']);
        await pegawaiBox.put(
            'alamat_unit_kerja', dataPegawai['alamat_unit_kerja']);
        await pegawaiBox.put('lattitude', dataPegawai['lattitude']);
        await pegawaiBox.put('longitude', dataPegawai['longitude']);
        await pegawaiBox.put('ukuran_radius', dataPegawai['ukuran_radius']);
        await pegawaiBox.put('satuan_radius', dataPegawai['satuan_radius']);
        await pegawaiBox.put('face_embeddings', dataPegawai['face_embeddings']);

        if (mounted) {
          showSuccessDialog(context, 'Data Pegawai Berhasil Diperbarui');
          Future.delayed(const Duration(seconds: 2), () {
            Navigator.pushNamed(context, '/');
          });
        }
      }
    }
  }

  Future<void> _loadTodayAttendance() async {
    final dashboardBox = await Hive.openBox('dashboard');
    final storedDate = dashboardBox.get('tanggal_presensi');
    final today = DateFormat('yyyy-MM-dd').format(DateTime.now());

    if (storedDate == today) {
      setState(() {
        checkin = dashboardBox.get('checkin', defaultValue: '-');
        checkout = dashboardBox.get('checkout', defaultValue: '-');
      });
    } else {
      setState(() {
        checkin = '-';
        checkout = '-';
      });
    }
  }

  Future<void> logout(BuildContext context) async {
    showLoadingDialog(context);
    final userBox = await Hive.openBox('userAndroid');
    final pegawaiBox = await Hive.openBox('pegawai');

    await userBox.clear();
    await pegawaiBox.clear();

    if (!context.mounted) return;
    Navigator.pushReplacementNamed(context, '/option');
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
        canPop: false,
        onPopInvoked: (didPop) {
          if (!didPop) {
            SystemNavigator.pop();
          }
        },
        child: Scaffold(
          backgroundColor: const Color(0xFFF0ECEC),
          drawer: _buildDrawer(context),
          appBar: AppBar(
            backgroundColor: Colors.blue,
            elevation: 4,
            title: const Text(
              "Presensi Online",
              style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.white),
            ),
            leading: Builder(
              builder: (context) => IconButton(
                icon: const Icon(Icons.menu, color: Colors.white),
                onPressed: () {
                  Scaffold.of(context).openDrawer();
                },
              ),
            ),
            actions: [
              Row(
                children: [
                  Image.asset(
                    'assets/images/pemkot_malang_logo.png',
                    height: 32,
                  ),
                  const SizedBox(width: 8),
                  CircleAvatar(
                    radius: 16,
                    backgroundImage: urlFoto != null
                        ? NetworkImage(baseUrl + urlFoto!)
                        : const AssetImage('assets/images/default_profile.png')
                            as ImageProvider,
                  ),
                  const SizedBox(width: 10),
                ],
              ),
            ],
          ),
          body: Stack(
            children: [
              Container(
                width: double.infinity,
                height: 260,
                padding: const EdgeInsets.all(16),
                color: Colors.blue,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircleAvatar(
                      radius: 40,
                      backgroundImage: urlFoto != null
                          ? NetworkImage(baseUrl + urlFoto!)
                          : const AssetImage(
                                  'assets/images/default_profile.png')
                              as ImageProvider,
                    ),
                    const SizedBox(height: 10),
                    Text(
                      "$namaPegawai",
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      "NIP: $nip",
                      style: const TextStyle(fontSize: 12, color: Colors.white),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      "$namaJabatan",
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                          fontSize: 14,
                          color: Colors.white,
                          fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                  ],
                ),
              ),
              SingleChildScrollView(
                child: Column(
                  children: [
                    const SizedBox(height: 220),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(10),
                          boxShadow: const [
                            BoxShadow(color: Colors.black12, blurRadius: 5),
                          ],
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.center,
                                children: [
                                  const Text(
                                    "Check In terakhir",
                                    style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFFABABAB)),
                                  ),
                                  const SizedBox(height: 5),
                                  Text(
                                    checkin,
                                    style: const TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold),
                                  ),
                                ],
                              ),
                            ),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.center,
                                children: [
                                  const Text(
                                    "Check Out terakhir",
                                    style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFFABABAB)),
                                  ),
                                  const SizedBox(height: 5),
                                  Text(
                                    checkout,
                                    style: const TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 10),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(10),
                          boxShadow: const [
                            BoxShadow(color: Colors.black12, blurRadius: 5),
                          ],
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.center,
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Flexible(
                              flex: 2,
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.start,
                                children: [
                                  const SizedBox(width: 12),
                                  _buildPieChart(100, Colors.blue, "100.0%"),
                                  const SizedBox(width: 12),
                                  _buildPieChart(0, Colors.red, "0.0%"),
                                ],
                              ),
                            ),
                            const SizedBox(width: 12),
                            Flexible(
                              flex: 2,
                              child: Padding(
                                padding: const EdgeInsets.only(right: 16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Align(
                                      alignment: Alignment.topLeft,
                                      child: Text(
                                        "Index Presensi ${DateFormat('MMMM', 'id_ID').format(DateTime.now())}",
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          color: Color(0xFFABABAB),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 10),
                                    Column(
                                      children: [
                                        _buildLegend(Colors.blue, "Kehadiran"),
                                        const SizedBox(width: 10),
                                        _buildLegend(Colors.red, "Sakit"),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 10),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Column(
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Expanded(
                                child: _buildButton(
                                  text: "Check In",
                                  icon: Icons.login,
                                  iconColor: Colors.blue,
                                  onTap: () => _showAbsensiModal(context, 0),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: _buildButton(
                                  text: "Check Out",
                                  icon: Icons.logout,
                                  iconColor: Colors.blue,
                                  onTap: () => _showAbsensiModal(context, 1),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Expanded(
                                child: _buildButton(
                                  text: "Laporan",
                                  icon: Icons.insert_chart,
                                  iconColor: Colors.blue,
                                  onTap: () {
                                    Navigator.pushNamed(context, '/');
                                  },
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: _buildButton(
                                  text: "Data Pegawai",
                                  icon: Icons.people,
                                  iconColor: Colors.blue,
                                  onTap: () {
                                    Navigator.pushNamed(context, '/');
                                  },
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ],
          ),
          bottomNavigationBar: BottomNavigationBar(
            currentIndex: 0,
            selectedItemColor: Colors.blue,
            unselectedItemColor: Colors.grey,
            type: BottomNavigationBarType.fixed,
            items: const [
              BottomNavigationBarItem(icon: Icon(Icons.home), label: "Home"),
              BottomNavigationBarItem(
                  icon: Icon(Icons.history), label: "Riwayat"),
              BottomNavigationBarItem(icon: Icon(Icons.chat), label: "Pesan"),
              BottomNavigationBarItem(icon: Icon(Icons.person), label: "Akun"),
            ],
          ),
        ));
  }

  Future<void> saveSelectedModel(FaceModelType model) async {
    final box = await Hive.openBox('settings');
    await box.put(hiveModelKey, model.name);
  }

  Future<FaceModelType> getSelectedModel() async {
    final box = await Hive.openBox('settings');
    final name =
        box.get(hiveModelKey, defaultValue: FaceModelType.facenet.name);
    return FaceModelType.values
        .firstWhere((e) => e.name == name, orElse: () => FaceModelType.facenet);
  }

  Future<void> showModelSelectionDialog(BuildContext context) async {
    FaceModelType selectedModel = await getSelectedModel();

    if (!context.mounted) return;

    await showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text("Pilih Model Ekstraksi"),
          content: StatefulBuilder(
            builder: (context, setState) {
              return Column(
                mainAxisSize: MainAxisSize.min,
                children: FaceModelType.values.map((model) {
                  return RadioListTile<FaceModelType>(
                    title: Text(model.name.toUpperCase()),
                    value: model,
                    groupValue: selectedModel,
                    onChanged: (FaceModelType? value) {
                      if (value != null) {
                        setState(() {
                          selectedModel = value;
                        });
                      }
                    },
                  );
                }).toList(),
              );
            },
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text("Batal"),
            ),
            ElevatedButton(
              onPressed: () async {
                await saveSelectedModel(selectedModel);

                if (!context.mounted) return;

                Navigator.of(context).pop();
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                      content: Text("Model disimpan: ${selectedModel.name}")),
                );
              },
              child: const Text("Simpan"),
            ),
          ],
        );
      },
    );
  }

  Widget _buildDrawer(BuildContext context) {
    return SizedBox(
      width: MediaQuery.of(context).size.width * 0.64,
      child: Drawer(
        backgroundColor: Colors.black87,
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              color: Colors.blue,
              width: double.infinity,
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 20,
                    backgroundImage: urlFoto != null
                        ? NetworkImage(baseUrl + urlFoto!)
                        : const AssetImage('assets/images/default_profile.png')
                            as ImageProvider,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      "$namaPegawai",
                      style: const TextStyle(
                        fontSize: 16,
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.only(top: 24),
                children: [
                  ListTile(
                    leading:
                        const Icon(Icons.calendar_today, color: Colors.white),
                    title: const Text("Riwayat",
                        style: TextStyle(color: Colors.white)),
                    onTap: () => Navigator.pushNamed(context, '/riwayat'),
                  ),
                  ListTile(
                    leading: const Icon(Icons.chat, color: Colors.white),
                    title: const Text("Pesan",
                        style: TextStyle(color: Colors.white)),
                    onTap: () => Navigator.pushNamed(context, '/pesan'),
                  ),
                  ListTile(
                    leading: const Icon(Icons.sync, color: Colors.white),
                    title: const Text("Perbarui Data",
                        style: TextStyle(color: Colors.white)),
                    onTap: () => showModelSelectionDialog(context),
                  ),
                ],
              ),
            ),
            Container(
              width: double.infinity,
              decoration: const BoxDecoration(
                color: Colors.red,
                borderRadius: BorderRadius.only(
                  topRight: Radius.circular(8),
                  bottomRight: Radius.circular(8),
                ),
              ),
              child: ListTile(
                leading: const Icon(Icons.logout, color: Colors.white),
                title:
                    const Text("Keluar", style: TextStyle(color: Colors.white)),
                onTap: () async {
                  final confirm = await showExitConfirmationDialog(context);

                  if (!context.mounted) return;

                  if (confirm == true) {
                    logout(context);
                  }
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<bool?> showExitConfirmationDialog(BuildContext context) {
    return showGeneralDialog<bool>(
      context: context,
      barrierDismissible: true,
      barrierLabel: MaterialLocalizations.of(context).modalBarrierDismissLabel,
      barrierColor: Colors.black54,
      transitionDuration: const Duration(milliseconds: 200),
      pageBuilder: (context, animation, secondaryAnimation) {
        return Center(
          child: Material(
            type: MaterialType.transparency,
            child: AlertDialog(
              title: const Text('Konfirmasi'),
              content: const Text('Apakah Anda yakin ingin keluar?'),
              actions: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    SizedBox(
                      height: 45,
                      child: TextButton(
                        style: TextButton.styleFrom(
                          backgroundColor: Colors.green,
                        ),
                        onPressed: () => Navigator.of(context).pop(false),
                        child: const Text('Batal',
                            style: TextStyle(color: Colors.white)),
                      ),
                    ),
                    const SizedBox(height: 10),
                    SizedBox(
                      height: 45,
                      child: TextButton(
                        style: TextButton.styleFrom(
                          backgroundColor: Colors.red,
                        ),
                        onPressed: () => Navigator.of(context).pop(true),
                        child: const Text('Keluar',
                            style: TextStyle(color: Colors.white)),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
      transitionBuilder: (context, animation, secondaryAnimation, child) {
        return Transform.scale(
          scale: animation.value,
          child: Opacity(
            opacity: animation.value,
            child: child,
          ),
        );
      },
    );
  }

  Widget _buildPieChart(int percentage, Color color, String label) {
    return Column(
      children: [
        SizedBox(
          width: 50,
          height: 50,
          child: Stack(
            alignment: Alignment.center,
            children: [
              SizedBox(
                width: 50,
                height: 50,
                child: CircularProgressIndicator(
                  value: percentage / 100,
                  backgroundColor: Colors.grey[300],
                  color: color,
                  strokeWidth: 5,
                ),
              ),
              Positioned.fill(
                child: Align(
                  alignment: Alignment.center,
                  child: Text(
                    label,
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: color,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildLegend(Color color, String label) {
    return Row(
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(
              color: color, borderRadius: BorderRadius.circular(2)),
        ),
        const SizedBox(width: 5),
        Text(label,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
      ],
    );
  }
}

Widget _buildButton({
  required String text,
  required IconData icon,
  required Color iconColor,
  required VoidCallback onTap,
}) {
  return GestureDetector(
    onTap: onTap,
    child: Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 5)],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: iconColor),
          const SizedBox(width: 8),
          Text(
            text,
            style: const TextStyle(
              color: Colors.black,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    ),
  );
}

void _showAbsensiModal(BuildContext context, int checkMode) {
  showGeneralDialog(
    context: context,
    barrierDismissible: true,
    barrierLabel: MaterialLocalizations.of(context).modalBarrierDismissLabel,
    barrierColor: Colors.black54,
    transitionDuration: const Duration(milliseconds: 200),
    pageBuilder: (context, animation, secondaryAnimation) {
      return Center(
        child: Material(
          type: MaterialType.transparency,
          child: AlertDialog(
            title: const Text('Pilih Jenis Absensi'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue,
                    ),
                    onPressed: () async {
                      final presensiBox = await Hive.openBox('presensi');
                      await presensiBox.put('check_mode', checkMode);
                      await presensiBox.put('jenis_absensi', 0);
                      if (!context.mounted) return;
                      Navigator.of(context).pop();
                      Navigator.pushNamed(context, '/location');
                    },
                    child: const Text('Reguler',
                        style: TextStyle(color: Colors.white)),
                  ),
                ),
                const SizedBox(height: 10),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green,
                    ),
                    onPressed: () async {
                      final presensiBox = await Hive.openBox('presensi');
                      await presensiBox.put('check_mode', checkMode);
                      await presensiBox.put('jenis_absensi', 1);
                      if (!context.mounted) return;
                      Navigator.of(context).pop();
                      Navigator.pushNamed(context, '/location');
                    },
                    child: const Text('DD / DL',
                        style: TextStyle(color: Colors.white)),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    },
    transitionBuilder: (context, animation, secondaryAnimation, child) {
      return Transform.scale(
        scale: animation.value,
        child: Opacity(
          opacity: animation.value,
          child: child,
        ),
      );
    },
  );
}

enum FaceModelType { facenet, ghostfacenet, mobilefacenet }

const String hiveModelKey = 'selected_face_model';
