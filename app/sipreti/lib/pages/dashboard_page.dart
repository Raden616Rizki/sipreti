import 'package:flutter/material.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:intl/intl.dart';
import 'package:sipreti/services/api_service.dart';

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
  final ApiService _apiService = ApiService();

  @override
  void initState() {
    super.initState();

    var box = Hive.box('userAndroid');
    var pegawaiBox = Hive.box('pegawai');

    setState(() {
      username = box.get('username');
      email = box.get('email');

      namaPegawai = pegawaiBox.get('nama');
      nip = pegawaiBox.get('nip');
      namaJabatan = pegawaiBox.get('nama_jabatan');
    });
  }

  Future<void> updatePegawaiData() async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator()),
    );

    var pegawaiBox = Hive.box('pegawai');
    String idPegawai = pegawaiBox.get('id_pegawai');

    Map<String, dynamic> dataPegawai = await _apiService.getPegawai(idPegawai);

    if (mounted) {
      if (dataPegawai["error"] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(dataPegawai["message"])),
        );
      } else {
        // await pegawaiBox.put('id_pegawai', dataPegawai['id_pegawai']);
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
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Data Pegawai Berhasil Diperbarui')),
          );

          Navigator.pushNamed(context, '/');
        }
      }
    }
  }

  Future<void> logout(BuildContext context) async {
    final userBox = await Hive.openBox('userAndroid');
    final pegawaiBox = await Hive.openBox('pegawai');

    await userBox.clear();
    await pegawaiBox.clear();

    if (!context.mounted) return;
    Navigator.pushReplacementNamed(context, '/option');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF0ECEC),
      drawer: _buildDrawer(context),
      appBar: AppBar(
        backgroundColor: Colors.blue,
        title: const Text(
          "Presensi Online",
          style: TextStyle(
              fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
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
          Container(
            width: double.infinity,
            height: 260,
            padding: const EdgeInsets.all(16),
            color: Colors.blue,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const CircleAvatar(
                  radius: 40,
                  backgroundImage:
                      AssetImage('assets/images/default_profile.png'),
                ),
                const SizedBox(height: 10),
                Text(
                  "$namaPegawai",
                  style: const TextStyle(
                    fontSize: 18,
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
                  style: const TextStyle(fontSize: 14, color: Colors.white),
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
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [
                              Text(
                                "Check In terakhir",
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFFABABAB)),
                              ),
                              SizedBox(height: 5),
                              Text(
                                "07:30",
                                style: TextStyle(
                                    fontSize: 16, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [
                              Text(
                                "Check Out terakhir",
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFFABABAB)),
                              ),
                              SizedBox(height: 5),
                              Text(
                                "-",
                                style: TextStyle(
                                    fontSize: 16, fontWeight: FontWeight.bold),
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
                              _buildPieChart(75, Colors.blue, "75.0%"),
                              const SizedBox(width: 12),
                              _buildPieChart(25, Colors.red, "25.0%"),
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
                                    "Index Presensi ${DateFormat('MMMM').format(DateTime.now())}",
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
                              onTap: () async {
                                final presensiBox =
                                    await Hive.openBox('presensi');
                                await presensiBox.put('check_mode', 0);
                                if (!context.mounted) return;
                                Navigator.pushNamed(context, '/location');
                              },
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: _buildButton(
                              text: "Check Out",
                              icon: Icons.logout,
                              iconColor: Colors.blue,
                              onTap: () async {
                                final presensiBox =
                                    await Hive.openBox('presensi');
                                await presensiBox.put('check_mode', 1);
                                if (!context.mounted) return;
                                Navigator.pushNamed(context, '/location');
                              },
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
          BottomNavigationBarItem(icon: Icon(Icons.history), label: "Riwayat"),
          BottomNavigationBarItem(icon: Icon(Icons.chat), label: "Pesan"),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: "Akun"),
        ],
      ),
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
                  const CircleAvatar(
                    radius: 20,
                    backgroundImage:
                        AssetImage('assets/images/default_profile.png'),
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
                    onTap: updatePegawaiData,
                  ),
                ],
              ),
            ),
            Container(
              // margin: const EdgeInsets.only(bottom: 64),
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
                  final confirm = await showDialog<bool>(
                    context: context,
                    builder: (BuildContext dialogContext) {
                      return AlertDialog(
                        title: const Text('Konfirmasi'),
                        content: const Text('Apakah Anda yakin ingin keluar?'),
                        actions: [
                          TextButton(
                            style: TextButton.styleFrom(
                              backgroundColor: Colors.green,
                            ),
                            onPressed: () =>
                                Navigator.of(dialogContext).pop(false),
                            child: const Text('Batal',
                                style: TextStyle(color: Colors.white)),
                          ),
                          TextButton(
                            style: TextButton.styleFrom(
                              backgroundColor: Colors.red,
                            ),
                            onPressed: () =>
                                Navigator.of(dialogContext).pop(true),
                            child: const Text('Keluar',
                                style: TextStyle(color: Colors.white)),
                          ),
                        ],
                      );
                    },
                  );

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
