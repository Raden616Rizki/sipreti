import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:latlong2/latlong.dart';
import 'package:geocoding/geocoding.dart';
import 'package:hive/hive.dart';
// import 'package:intl/intl.dart';

class LocationPage extends StatefulWidget {
  const LocationPage({super.key});

  @override
  State<LocationPage> createState() => _LocationPageState();
}

class _LocationPageState extends State<LocationPage> {
  final MapController mapController = MapController();

  LatLng? currentLocation;
  String? currentAddress;

  LatLng kantorLocation = const LatLng(-7.9437612, 112.6143654);
  double radiusMeter = 1280.0;
  bool isWithinRadius = false;
  int? jenisAbsensi;

  String? urlFoto;
  final String baseUrl = 'http://35.187.225.70/sipreti/uploads/foto_pegawai/';

  bool isProcessing = true;

  @override
  void initState() {
    super.initState();
    loadPegawaiData();
    _getCurrentLocation();

    var pegawaiBox = Hive.box('pegawai');
    var presensiBox = Hive.box('presensi');
    setState(() {
      urlFoto = pegawaiBox.get('url_foto');
      jenisAbsensi = presensiBox.get('jenis_absensi');
      isProcessing = false;
    });
  }

  Future<void> loadPegawaiData() async {
    final pegawaiBox = await Hive.openBox('pegawai');

    final double latitude =
        double.tryParse(pegawaiBox.get('lattitude')?.toString() ?? '') ??
            -7.9437612;
    final double longitude =
        double.tryParse(pegawaiBox.get('longitude')?.toString() ?? '') ??
            112.6143654;
    final double radius =
        double.tryParse(pegawaiBox.get('ukuran_radius')?.toString() ?? '') ??
            1280.0;

    setState(() {
      kantorLocation = LatLng(latitude, longitude);
      radiusMeter = radius;
    });
  }

  Future<void> _getCurrentLocation() async {
    LocationPermission permission;

    permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) return;
    }

    if (permission == LocationPermission.deniedForever) return;

    Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high);

    List<Placemark> placemarks = await placemarkFromCoordinates(
      position.latitude,
      position.longitude,
    );

    final place = placemarks.first;
    final userLocation = LatLng(position.latitude, position.longitude);

    const distance = Distance();
    final double jarak = distance(userLocation, kantorLocation);

    setState(() {
      currentLocation = userLocation;
      currentAddress =
          "${place.name}, ${place.street}, ${place.subLocality}, ${place.locality}, ${place.administrativeArea}, ${place.postalCode}, ${place.country}";
      isWithinRadius = jarak <= radiusMeter;
    });

    mapController.move(currentLocation!, 17);
  }

  Future<void> saveAttendance({
    required String currentAddress,
    required double latitude,
    required double longitude,
  }) async {
    final presensiBox = await Hive.openBox('presensi');
    // final checkMode = presensiBox.get('check_mode');
    // debugPrint(checkMode.toString());

    final DateTime now = DateTime.now();

    await presensiBox.put('nama_lokasi', currentAddress);
    await presensiBox.put('lattitude', latitude);
    await presensiBox.put('longitude', longitude);
    await presensiBox.put('waktu_absensi', now);

    if (mounted) {
      Navigator.pushNamed(context, '/biometric');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.blue,
        elevation: 4,
        title: const Text(
          "Lokasi Absen",
          style: TextStyle(
              fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
        ),
        automaticallyImplyLeading: false,
        actions: [
          Row(
            children: [
              Image.asset(
                'assets/images/pemkot_malang_logo.png',
                height: 32,
              ),
              const SizedBox(width: 8),
              CircleAvatar(
                backgroundImage: urlFoto != null
                    ? NetworkImage(baseUrl + urlFoto!)
                    : const AssetImage('assets/images/default_profile.png')
                        as ImageProvider,
                radius: 16,
              ),
              const SizedBox(width: 10),
            ],
          ),
        ],
      ),
      body: Stack(
        children: [
          FlutterMap(
            mapController: mapController,
            options: const MapOptions(
              initialCenter: LatLng(-7.9437612, 112.6143654),
              initialZoom: 16,
            ),
            children: [
              TileLayer(
                urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'com.example.app',
              ),
              if (jenisAbsensi != 1)
                CircleLayer(
                  circles: [
                    CircleMarker(
                      point: kantorLocation,
                      radius: radiusMeter,
                      useRadiusInMeter: true,
                      color: Colors.blue.withOpacity(0.2),
                    ),
                  ],
                ),
              MarkerLayer(
                markers: [
                  if (jenisAbsensi != 1)
                    Marker(
                      point: kantorLocation,
                      width: 40,
                      height: 40,
                      child: const Icon(
                        Icons.location_pin,
                        color: Colors.blue,
                        size: 40,
                      ),
                    ),
                  if (currentLocation != null)
                    Marker(
                      point: currentLocation!,
                      width: 40,
                      height: 40,
                      child: const Icon(
                        Icons.location_pin,
                        color: Colors.red,
                        size: 40,
                      ),
                    ),
                ],
              ),
            ],
          ),
          Positioned(
            top: 16,
            right: 12,
            child: Column(
              children: [
                _buildPinLegend(Icons.location_pin, "Lokasi Anda", Colors.red),
                const SizedBox(height: 8),
                _buildPinLegend(
                    Icons.location_pin, "Lokasi Absen", Colors.blue),
              ],
            ),
          ),
        ],
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          boxShadow: [
            BoxShadow(
              color: Colors.black12,
              blurRadius: 8,
              offset: Offset(0, -2),
            ),
          ],
        ),
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text("Lokasi Anda",
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            const SizedBox(height: 4),
            Text(
              currentAddress ?? "Mengambil lokasi...",
              style: const TextStyle(color: Colors.blue, fontSize: 14),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: ElevatedButton(
                    onPressed: () => Navigator.pop(context),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(30),
                      ),
                    ),
                    child: const Text("Kembali",
                        style: TextStyle(color: Colors.white)),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: ElevatedButton(
                    onPressed:
                        (jenisAbsensi == 1 || isWithinRadius && !isProcessing)
                            ? () async {
                                await saveAttendance(
                                  currentAddress: currentAddress!,
                                  latitude: currentLocation!.latitude,
                                  longitude: currentLocation!.longitude,
                                );
                              }
                            : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(30),
                      ),
                    ),
                    child: const Text(
                      "Konfirmasi",
                      style: TextStyle(color: Colors.white),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
      floatingActionButton: Container(
        margin: const EdgeInsets.only(bottom: 8, right: 8),
        child: FloatingActionButton(
          onPressed: _getCurrentLocation,
          backgroundColor: Colors.white,
          mini: true,
          child: const Icon(Icons.my_location, color: Colors.black),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
    );
  }

  Widget _buildPinLegend(IconData icon, String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
        boxShadow: const [
          BoxShadow(
            color: Colors.black12,
            blurRadius: 4,
            offset: Offset(0, 2),
          )
        ],
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(width: 4),
          Text(label,
              style:
                  const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }
}
