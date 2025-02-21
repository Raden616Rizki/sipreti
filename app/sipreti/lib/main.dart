import 'package:flutter/material.dart';
import 'package:sipreti/pages/dashboard_page.dart';
import 'package:sipreti/pages/form_page.dart';
import 'package:sipreti/pages/nip_page.dart';
import 'package:sipreti/pages/option_page.dart';

Future<void> main() async {
  const initialRoute = '/';

  runApp(MaterialApp(
    initialRoute: initialRoute,
    routes: {
      '/': (context) => const DashboardPage(),
      '/option': (context) => const OptionPage(),
      '/nip': (context) => const NIPPage(),
      '/form': (context) => const FormPage(),
    },
  ));
}
