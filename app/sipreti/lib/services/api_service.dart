import 'dart:io';
import 'dart:convert';
import 'package:flutter_neumorphic_plus/flutter_neumorphic.dart';
import 'package:http/http.dart' as http;

class ApiService {
  // final String baseUrl = "http://127.0.0.1/sipreti";
  final String baseUrl = "http://35.187.225.70/sipreti";
  final String baseUrlDjango = "http://35.187.225.70:8000/attendance";
  // final String baseUrlDjango = "http://127.0.0.1:8000/attendance";

  Future<Map<String, dynamic>> validateNip(String nip) async {
    final String url = "$baseUrl/pegawai/validate_nip?nip=$nip";

    try {
      final response = await http.get(Uri.parse(url));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          "error": true,
          "message": "Error: ${response.statusCode} - ${response.body}"
        };
      }
    } catch (e) {
      return {"error": true, "message": "Exception: $e"};
    }
  }

  Future<Map<String, dynamic>> registerUser({
    required String idPegawai,
    required String username,
    required String password,
    required String email,
    required String noHp,
    required String imei,
    required String validHp,
  }) async {
    final String url = "$baseUrl/user_android/create_api";

    try {
      var request = http.MultipartRequest('POST', Uri.parse(url));
      request.fields['id_pegawai'] = idPegawai;
      request.fields['username'] = username;
      request.fields['password'] = password;
      request.fields['email'] = email;
      request.fields['no_hp'] = noHp;
      request.fields['imei'] = imei;
      request.fields['valid_hp'] = validHp;

      var response = await request.send();
      var responseBody = await response.stream.bytesToString();

      if (response.statusCode == 200) {
        return jsonDecode(responseBody);
      } else {
        debugPrint("Error: ${response.statusCode} - $responseBody");
        return {
          "error": true,
          "message": "Error: ${response.statusCode} - $responseBody"
        };
      }
    } catch (e) {
      return {"error": true, "message": "Exception: $e"};
    }
  }

  Future<Map<String, dynamic>> loginUser(
      String username, String password) async {
    final String url = "$baseUrl/user_android/login_api";

    try {
      var request = http.MultipartRequest('POST', Uri.parse(url));
      request.fields['username'] = username;
      request.fields['password'] = password;

      var response = await request.send();
      var responseBody = await response.stream.bytesToString();

      if (response.statusCode == 200) {
        return jsonDecode(responseBody);
      } else {
        return {
          "error": true,
          "message": "Error: ${response.statusCode} - $responseBody"
        };
      }
    } catch (e) {
      return {"error": true, "message": "Exception: $e"};
    }
  }

  Future<Map<String, dynamic>> getPegawai(String idPegawai) async {
    final String url = "$baseUrl/pegawai/read_api/$idPegawai";

    try {
      final response = await http.get(Uri.parse(url));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          "error": true,
          "message": "Error: ${response.statusCode} - ${response.body}"
        };
      }
    } catch (e) {
      return {"error": true, "message": "Exception: $e"};
    }
  }

  Future<Map<String, dynamic>> faceVerification(
    String idPegawai,
    List<double> vektorPresensi,
  ) async {
    final String url = "$baseUrlDjango/face-verification/";

    try {
      final response = await http.post(
        Uri.parse(url),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'id_pegawai': idPegawai,
          'vektor_presensi': vektorPresensi,
        }),
      );

      if (response.statusCode == 200) {
        debugPrint(response.body);
        return {
          "error": false,
          "data": jsonDecode(response.body),
        };
      } else {
        return {
          "error": true,
          "message": "Error: ${response.statusCode} - ${response.body}",
        };
      }
    } catch (e) {
      return {
        "error": true,
        "message": "Exception: $e",
      };
    }
  }

  Future<Map<String, dynamic>> storeAttendance({
    required int jenisAbsensi,
    required int idPegawai,
    required int checkMode,
    required String waktuAbsensi,
    required double latitude,
    required double longitude,
    required String namaLokasi,
    required String lamaAbsensi,
    required String jarakVektor,
    required File fotoPresensi,
    File? dokumen,
  }) async {
    final String url = "$baseUrl/log_absensi/create_api";

    try {
      var request = http.MultipartRequest('POST', Uri.parse(url));
      request.fields['jenis_absensi'] = jenisAbsensi.toString();
      request.fields['id_pegawai'] = idPegawai.toString();
      request.fields['check_mode'] = checkMode.toString();
      request.fields['waktu_absensi'] = waktuAbsensi;
      request.fields['lattitude'] = latitude.toString();
      request.fields['longitude'] = longitude.toString();
      request.fields['nama_lokasi'] = namaLokasi;
      request.fields['waktu_verifikasi'] = lamaAbsensi;
      request.fields['jarak_vektor'] = jarakVektor;

      request.files.add(
        await http.MultipartFile.fromPath(
            'url_foto_presensi', fotoPresensi.path),
      );

      if (dokumen != null) {
        request.files.add(
          await http.MultipartFile.fromPath('url_dokumen', dokumen.path),
        );
      }

      var response = await request.send();
      var responseBody = await response.stream.bytesToString();

      if (response.statusCode == 200) {
        return jsonDecode(responseBody);
      } else {
        return {
          "error": true,
          "message": "Error: ${response.statusCode} - $responseBody"
        };
      }
    } catch (e) {
      return {"error": true, "message": "Exception: $e"};
    }
  }
}
