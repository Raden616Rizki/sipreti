// import 'package:flutter/material.dart';
import 'package:flutter_neumorphic_plus/flutter_neumorphic.dart';
import 'package:sipreti/services/api_service.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'dart:convert';

import 'package:sipreti/utils/dialog.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  LoginPageState createState() => LoginPageState();
}

class LoginPageState extends State<LoginPage> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final ApiService _apiService = ApiService();
  bool _isPasswordVisible = false;

  void _loginUser() async {
    showLoadingDialog(context);

    String email = _emailController.text.trim();
    String password = _passwordController.text.trim();

    Map<String, dynamic> result = await _apiService.loginUser(email, password);

    if (!mounted) return;

    if (result["error"] == true) {
      Navigator.of(context).pop();
      final String message = extractMessage(result["message"]);
      await showErrorDialog(context, message);
      return;
    } else {
      final userData = result['data'];

      var box = Hive.box('userAndroid');

      await box.put('id_user_android', userData['id_user_android']);
      await box.put('id_pegawai', userData['id_pegawai']);
      await box.put('username', userData['username']);
      await box.put('email', userData['email']);
      await box.put('no_hp', userData['no_hp']);

      await getPegawaiData(userData['id_pegawai']);
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

  Future<bool> _validateForm() async {
    if (_emailController.text.trim().isEmpty) {
      await showErrorDialog(context, "Email tidak boleh kosong.");
      return false;
    }

    if (!isEmailValid(_emailController.text.trim())) {
      await showErrorDialog(context, "Format email tidak valid.");
      return false;
    }

    if (_passwordController.text.trim().isEmpty) {
      await showErrorDialog(context, "Password tidak boleh kosong.");
      return false;
    }

    return true;
  }

  bool isEmailValid(String email) {
    final regex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
    return regex.hasMatch(email);
  }

  Future<void> getPegawaiData(String idPegawai) async {
    Map<String, dynamic> dataPegawai = await _apiService.getPegawai(idPegawai);

    if (mounted) {
      if (dataPegawai["error"] == true) {
        final String message = extractMessage(dataPegawai["message"]);
        await showErrorDialog(context, message);
        return;
      } else {
        var pegawaiBox = Hive.box('pegawai');

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
          showSuccessDialog(context, 'Berhasil Masuk');
          Future.delayed(const Duration(seconds: 2), () {
            Navigator.pushNamed(context, '/');
          });
        }
      }
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
                    bottom: MediaQuery.of(context).viewInsets.bottom),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const SizedBox(height: 60),
                    const Text(
                      "Masuk",
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
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
                        controller: _emailController,
                        decoration: InputDecoration(
                          hintText: "Email",
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
                            Icons.email,
                            color: Colors.grey,
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            vertical: 16,
                          ),
                        ),
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
                        controller: _passwordController,
                        obscureText:
                            !_isPasswordVisible, // Mengatur visibilitas password
                        decoration: InputDecoration(
                          hintText: "Password",
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
                            Icons.lock,
                            color: Colors.grey,
                          ),
                          suffixIcon: IconButton(
                            icon: Icon(
                              _isPasswordVisible
                                  ? Icons.visibility
                                  : Icons.visibility_off,
                              color: Colors.grey,
                            ),
                            onPressed: () {
                              setState(() {
                                _isPasswordVisible = !_isPasswordVisible;
                              });
                            },
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
                        onPressed: () async {
                          bool isValid = await _validateForm();
                          if (!isValid) return;

                          _loginUser();
                        },
                        child: const Text(
                          "MASUK",
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
