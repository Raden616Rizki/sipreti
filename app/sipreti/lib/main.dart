import 'package:flutter/material.dart';
import 'package:sipreti/pages/biometric_page.dart';
import 'package:sipreti/pages/dashboard_page.dart';
// import 'package:sipreti/pages/location_page.dart';
import 'package:sipreti/pages/attendance_page.dart';
import 'package:sipreti/pages/form_page.dart';
import 'package:sipreti/pages/nip_page.dart';
import 'package:sipreti/pages/option_page.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      // debugShowCheckedModeBanner: false,
      title: 'Sipreti App',
      // initialRoute: '/location',
      initialRoute: '/',
      routes: {
        '/': (context) => const DashboardPage(),
        // '/location': (context) => const LocationPage(),
        '/biometric': (context) => const BiometricPage(),
        '/attendance': (context) => const AttendancePage(),
        '/option': (context) => const OptionPage(),
        '/nip': (context) => const NIPPage(),
        '/form': (context) => const FormPage(),
      },
    );
  }
}
