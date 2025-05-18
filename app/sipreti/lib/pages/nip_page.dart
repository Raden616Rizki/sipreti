import 'package:flutter_neumorphic_plus/flutter_neumorphic.dart';
import 'package:sipreti/services/api_service.dart';
import 'dart:convert';
import 'package:sipreti/utils/dialog.dart';

class NIPPage extends StatefulWidget {
  const NIPPage({super.key});

  @override
  NIPPageState createState() => NIPPageState();
}

class NIPPageState extends State<NIPPage> {
  final TextEditingController _nipController = TextEditingController();
  final ApiService _apiService = ApiService();

  void _loadNIP() async {
    String nip = _nipController.text.trim();
    if (nip.isEmpty) {
      await showErrorDialog(context, "Masukkan NIP terlebih dahulu");
      return;
    }

    Map<String, dynamic> result = await _apiService.validateNip(nip);

    if (!mounted) return;

    if (result["error"] == true) {
      final String message = extractMessage(result["message"]);
      await showErrorDialog(context, message);
      return;
    } else {
      if (mounted) {
        Navigator.pushNamed(
          context,
          '/form',
          arguments: {
            'id_pegawai': result['data']['id_pegawai'],
            'nama': result['data']['nama'],
            'nip': result['data']['nip'],
          },
        );
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

  @override
  Widget build(BuildContext context) {
    final double heightScreen = MediaQuery.of(context).size.height;

    return Scaffold(
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          Positioned.fill(
            child: Align(
              alignment: Alignment.topCenter,
              child: Image.asset(
                'assets/images/pemkot_malang_bg_sm.png',
                fit: BoxFit.cover,
                width: double.infinity,
              ),
            ),
          ),
          Positioned(
            top: (100 / 2) + 40,
            left: 0,
            right: 0,
            child: Container(
              width: double.infinity,
              height: heightScreen - ((80 / 2) + 40),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 30),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(16),
                  topRight: Radius.circular(16),
                ),
              ),
              child: SingleChildScrollView(
                padding: EdgeInsets.only(
                  bottom: MediaQuery.of(context).viewInsets.bottom,
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const SizedBox(height: 60),
                    const Text(
                      "Buat Akun Baru",
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 40),
                    const Text(
                      "Load Pegawai by NIP",
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Colors.blue,
                      ),
                    ),
                    const SizedBox(height: 25),
                    Neumorphic(
                      style: NeumorphicStyle(
                        depth: -3,
                        intensity: 0.6,
                        color: Colors.grey[200],
                        boxShape: NeumorphicBoxShape.roundRect(
                          BorderRadius.circular(10),
                        ),
                      ),
                      child: TextField(
                        controller: _nipController,
                        decoration: InputDecoration(
                          hintText: "Masukkan NIP Baru / NIP Lama",
                          hintStyle: const TextStyle(
                            color: Colors.grey,
                            fontWeight: FontWeight.bold,
                          ),
                          filled: true,
                          fillColor: Colors.grey[100],
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: BorderSide.none,
                          ),
                          prefixIcon: const Icon(
                            Icons.person,
                            color: Colors.grey,
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            vertical: 16,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 30),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blue,
                          padding: const EdgeInsets.symmetric(vertical: 15),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                          shadowColor: Colors.black,
                          elevation: 8,
                        ),
                        onPressed: _loadNIP,
                        child: const Text(
                          "LOAD NIP",
                          style: TextStyle(
                              fontSize: 16,
                              color: Colors.white,
                              fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          Positioned(
            top: 40,
            left: 0,
            right: 0,
            child: Column(
              children: [
                Stack(
                  alignment: Alignment.center,
                  children: [
                    const SizedBox(width: 100, height: 100),
                    Image.asset(
                      'assets/images/pemkot_malang_logo.png',
                      width: 80,
                      height: 80,
                    ),
                  ],
                ),
                const Text(
                  "Pemkot Malang",
                  style: TextStyle(
                    fontSize: 8,
                    fontWeight: FontWeight.bold,
                    color: Colors.black,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
