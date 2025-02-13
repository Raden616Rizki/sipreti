import 'package:flutter/material.dart';
import 'package:sipreti/pages/option_page.dart';

Future<void> main() async {
  const initialRoute = '/option';

  runApp(MaterialApp(
    initialRoute: initialRoute,
    routes: {
      '/option': (context) => const OptionPage(),
    },
  ));
}
