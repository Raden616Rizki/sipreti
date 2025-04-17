import 'package:flutter/material.dart';
// import 'package:sipreti/pages/biometric_page.dart';
import 'package:sipreti/pages/dashboard_page.dart';
import 'package:sipreti/pages/location_page.dart';
import 'package:sipreti/pages/attendance_page.dart';
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

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      // debugShowCheckedModeBanner: false,
      title: 'Sipreti App',
      // initialRoute: '/location',
      initialRoute: '/login',
      routes: {
        '/': (context) => const DashboardPage(),
        '/location': (context) => const LocationPage(),
        // '/biometric': (context) => const BiometricPage(),
        '/option': (context) => const OptionPage(),
        '/login': (context) => const LoginPage(),
        '/attendance': (context) => const AttendancePage(),
        '/nip': (context) => const NIPPage(),
        '/form': (context) => const FormPage(),
      },
    );
  }
}
