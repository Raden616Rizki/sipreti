import 'package:flutter/material.dart';
import 'package:sipreti/pages/dashboard_page.dart';
import 'package:sipreti/pages/form_page.dart';
import 'package:sipreti/pages/login_page.dart';
import 'package:sipreti/pages/nip_page.dart';
import 'package:sipreti/pages/option_page.dart';
import 'package:hive_flutter/hive_flutter.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Hive.initFlutter();
  await Hive.openBox('userAndroid');
  await Hive.openBox('pegawai');

  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  bool _hasUserData() {
    final userBox = Hive.box('userAndroid');
    final pegawaiBox = Hive.box('pegawai');
    return userBox.isNotEmpty && pegawaiBox.isNotEmpty;
  }

  @override
  Widget build(BuildContext context) {
    final bool hasUserData = _hasUserData();

    return MaterialApp(
      title: 'Sipreti App',
      initialRoute: hasUserData ? '/' : '/option',
      routes: {
        '/': (context) => const DashboardPage(),
        '/option': (context) => const OptionPage(),
        '/login': (context) => const LoginPage(),
        '/nip': (context) => const NIPPage(),
        '/form': (context) => const FormPage(),
      },
    );
  }
}
