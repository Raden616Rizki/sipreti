import 'package:flutter/material.dart';
import 'package:sipreti/pages/biometric_page.dart';
import 'package:sipreti/pages/dashboard_page.dart';
import 'package:sipreti/pages/location_page.dart';
import 'package:sipreti/pages/attendance_page.dart';
import 'package:sipreti/pages/form_page.dart';
import 'package:sipreti/pages/nip_page.dart';
import 'package:sipreti/pages/option_page.dart';

Future<void> main() async {
  const initialRoute = '/option';

  runApp(MaterialApp(
    initialRoute: initialRoute,
    routes: {
      '/': (context) => const DashboardPage(),
      '/location': (context) => const LocationPage(),
      '/biometric': (context) => const BiometricPage(),
      '/attendance': (context) => const AttendancePage(),
      '/option': (context) => const OptionPage(),
      '/nip': (context) => const NIPPage(),
      '/form': (context) => const FormPage(),
    },
  ));
}
