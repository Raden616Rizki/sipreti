import 'dart:convert';
import 'package:flutter_neumorphic_plus/flutter_neumorphic.dart';
import 'package:http/http.dart' as http;

class ApiService {
  // final String baseUrl = "http://127.0.0.1/sipreti";
  final String baseUrl = "http://192.168.1.115/sipreti";
  final String baseUrlDjango = "http://192.168.1.115:8000/attendance";
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

  Future<Map<String, dynamic>> loginUser(String email, String password) async {
    final String url = "$baseUrl/user_android/login_api";

    try {
      var request = http.MultipartRequest('POST', Uri.parse(url));
      request.fields['email'] = email;
      request.fields['password'] = password;

      var response = await request.send();
      var responseBody = await response.stream.bytesToString();

      debugPrint(responseBody);

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
    final String url = "$baseUrl/pegawai/read/$idPegawai";

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

      debugPrint('Status: ${response.statusCode}');
      debugPrint('Response body: ${response.body}');

      if (response.statusCode == 200) {
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
}
