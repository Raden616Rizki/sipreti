import 'package:flutter/material.dart';
import 'package:sipreti/pages/attendance_page.dart';
import 'package:sipreti/pages/dashboard_page.dart';
import 'package:sipreti/pages/form_page.dart';
import 'package:sipreti/pages/nip_page.dart';
import 'package:sipreti/pages/option_page.dart';

Future<void> main() async {
  const initialRoute = '/location';

  runApp(MaterialApp(
    initialRoute: initialRoute,
    routes: {
      '/': (context) => const DashboardPage(),
      '/location': (context) => const DashboardPage(),
      '/attendance': (context) => const AttendancePage(),
      '/option': (context) => const OptionPage(),
      '/nip': (context) => const NIPPage(),
      '/form': (context) => const FormPage(),
    },
  ));
}
