import 'package:flutter_neumorphic_plus/flutter_neumorphic.dart';
import 'package:sipreti/services/api_service.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter_device_imei/flutter_device_imei.dart';
import 'dart:io';
import 'dart:convert';
import 'package:android_id/android_id.dart';
import 'package:sipreti/utils/dialog.dart';

class FormPage extends StatefulWidget {
  const FormPage({super.key});

  @override
  FormPageState createState() => FormPageState();
}

class FormPageState extends State<FormPage> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _noTelephoneController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController =
      TextEditingController();

  int? idPegawai;

  bool _isPasswordVisible = false;
  bool _isConfirmPasswordVisible = false;

  final ApiService apiService = ApiService();

  void submitRegistration() async {
    final email = _emailController.text.trim();
    final username = _nameController.text.trim();
    final noHp = _noTelephoneController.text.trim();
    final password = _passwordController.text.trim();

    showLoadingDialog(context);

    try {
      String imei = "Unknown";
      String validHp = "Unknown";

      final deviceInfo = DeviceInfoPlugin();

      if (Platform.isAndroid) {
        final androidInfo = await deviceInfo.androidInfo;
        validHp = "${androidInfo.manufacturer} ${androidInfo.model}";

        if (androidInfo.version.sdkInt < 29) {
          imei = await FlutterDeviceImei.instance.getIMEI() ?? "Unknown";
        } else {
          const androidIdPlugin = AndroidId();
          final androidId = await androidIdPlugin.getId();
          imei = androidId!;
        }
      } else if (Platform.isIOS) {
        final iosInfo = await deviceInfo.iosInfo;
        validHp = "${iosInfo.name} ${iosInfo.model}";
        imei = iosInfo.identifierForVendor ?? "Unknown";
      }

      final result = await apiService.registerUser(
        idPegawai: idPegawai.toString(),
        username: username,
        password: password,
        email: email,
        noHp: noHp,
        imei: imei,
        validHp: validHp,
      );

      if (mounted) Navigator.pop(context);

      if (mounted) {
        if (result['error'] == true) {
          Navigator.of(context).pop();
          final String message = extractMessage(result["message"]);
          await showErrorDialog(context, message);
          return;
        } else {
          showSuccessDialog(context, 'Registrasi Berhasil');
          Future.delayed(const Duration(seconds: 2), () {
            Navigator.pushNamed(context, '/login');
          });
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context);
        await showErrorDialog(context, 'Terjadi Kesalahan');
        return;
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

  Future<bool> _validateForm() async {
    if (_emailController.text.trim().isEmpty) {
      await showErrorDialog(context, "Email tidak boleh kosong.");
      return false;
    }

    if (!isEmailValid(_emailController.text.trim())) {
      await showErrorDialog(context, "Format email tidak valid.");
      return false;
    }

    if (_noTelephoneController.text.trim().isEmpty) {
      await showErrorDialog(context, "Nomor Telepon tidak boleh kosong.");
      return false;
    }

    if (_nameController.text.trim().isEmpty) {
      await showErrorDialog(context, "Nama Pegawai tidak boleh kosong.");
      return false;
    }

    if (_passwordController.text.trim().isEmpty) {
      await showErrorDialog(context, "Password tidak boleh kosong.");
      return false;
    }

    if (_confirmPasswordController.text.trim().isEmpty) {
      await showErrorDialog(context, "Konfirmasi Password tidak boleh kosong.");
      return false;
    }

    if (_passwordController.text != _confirmPasswordController.text) {
      await showErrorDialog(
          context, "Password dan Konfirmasi Password tidak sama.");
      return false;
    }

    return true;
  }

  bool isEmailValid(String email) {
    final regex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
    return regex.hasMatch(email);
  }

  @override
  Widget build(BuildContext context) {
    final double heightScreen = MediaQuery.of(context).size.height;
    final Object? args = ModalRoute.of(context)?.settings.arguments;
    final Map<String, dynamic>? data =
        args is Map<String, dynamic> ? args : null;

    if (data == null ||
        !data.containsKey('id_pegawai') ||
        !data.containsKey('nama') ||
        !data.containsKey('nip')) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        Navigator.pushReplacementNamed(context, '/nip');
      });

      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    } else {
      setState(() {
        idPegawai = int.tryParse(data['id_pegawai'].toString());
      });
    }

    final nama = data['nama'];
    final nip = data['nip'];

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
                      "Buat Akun Baru",
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 20),
                    Text(
                      nama ?? "Nama tidak tersedia",
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Colors.black,
                      ),
                    ),
                    Text(
                      nip ?? "NIP tidak tersedia",
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.normal,
                        color: Colors.black,
                      ),
                    ),
                    const SizedBox(height: 32),
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
                    const SizedBox(height: 16),
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
                        controller: _noTelephoneController,
                        decoration: InputDecoration(
                          hintText: "No. Telepon",
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
                            Icons.contacts,
                            color: Colors.grey,
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            vertical: 16,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
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
                        controller: _nameController,
                        decoration: InputDecoration(
                          hintText: "Username",
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
                    const SizedBox(height: 12),
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
                        obscureText: !_isPasswordVisible,
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
                    const SizedBox(height: 12),
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
                        controller: _confirmPasswordController,
                        obscureText: !_isConfirmPasswordVisible,
                        decoration: InputDecoration(
                          hintText: "Konfirmasi Password",
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
                              _isConfirmPasswordVisible
                                  ? Icons.visibility
                                  : Icons.visibility_off,
                              color: Colors.grey,
                            ),
                            onPressed: () {
                              setState(() {
                                _isConfirmPasswordVisible =
                                    !_isConfirmPasswordVisible;
                              });
                            },
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            vertical: 16,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),
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

                          submitRegistration();
                        },
                        child: const Text(
                          "DAFTAR SEKARANG",
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
