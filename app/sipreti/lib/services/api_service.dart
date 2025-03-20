import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  final String baseUrl = "http://127.0.0.1/sipreti";

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
}
