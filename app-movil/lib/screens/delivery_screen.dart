import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart';
import 'package:geolocator/geolocator.dart';
import '../services/package_service.dart';

class DeliveryScreen extends StatefulWidget {
  final Map<String, dynamic> package;
  final int userId;

  const DeliveryScreen({
    super.key,
    required this.package,
    required this.userId,
  });

  @override
  State<DeliveryScreen> createState() => _DeliveryScreenState();
}

class _DeliveryScreenState extends State<DeliveryScreen> {
  Position? position;
  XFile? imageFile;
  Uint8List? imageBytes;
  final picker = ImagePicker();
  final packageService = PackageService();
  bool completing = false;

  @override
  void initState() {
    super.initState();
    getPosition();
  }

  Future<void> getPosition() async {
    try {
      LocationPermission permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        _showError("Permiso de ubicación denegado");
        return;
      }

      position = await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.high);
      setState(() {});
    } catch (e) {
      _showError("Error obteniendo ubicación: $e");
    }
  }

  Future<void> pickPhoto() async {
    try {
      final picked = await picker.pickImage(source: ImageSource.camera);
      if (picked != null) {
        // Leer bytes para mostrar en web y en móvil (Image.memory funciona en ambos)
        final bytes = await picked.readAsBytes();
        setState(() {
          imageFile = picked;
          imageBytes = bytes;
        });
      }
    } catch (e) {
      _showError("Error al tomar la foto: $e");
    }
  }

  Future<void> completeDelivery() async {
    if (imageFile == null) {
      _showError("Debes tomar una foto del paquete");
      return;
    }
    if (position == null) {
      _showError("Ubicación no disponible");
      return;
    }

    setState(() => completing = true);

    try {
      bool ok = await packageService.completeDelivery(
        packageId: widget.package["id"],
        userId: widget.userId,
        photo: imageFile,
        lat: position!.latitude,
        lng: position!.longitude,
      );

      if (ok && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Entrega registrada con éxito")),
        );
        Navigator.pop(context);
      } else {
        _showError("No se pudo registrar la entrega");
      }
    } catch (e) {
      _showError("Error al completar entrega: $e");
    } finally {
      setState(() => completing = false);
    }
  }

  void _showError(String msg) {
    if (!mounted) return;
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text("Error"),
        content: Text(msg),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("OK"),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (position == null) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(title: Text("Entrega Paquete #${widget.package["id"]}")),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12.0),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: SizedBox(
                height: 300,
                child: Card(
                  margin: EdgeInsets.zero,
                  child: FlutterMap(
                    options: MapOptions(
                      initialCenter:
                          LatLng(position!.latitude, position!.longitude),
                      initialZoom: 16,
                    ),
                    children: [
                      TileLayer(
                        urlTemplate:
                            "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
                        userAgentPackageName: 'com.example.tuapp',
                      ),
                      MarkerLayer(
                        markers: [
                          Marker(
                            point:
                                LatLng(position!.latitude, position!.longitude),
                            width: 60,
                            height: 60,
                            child: const Icon(
                              Icons.person_pin_circle,
                              size: 40,
                              color: Colors.blue,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
          if (imageFile != null && imageBytes != null)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Image.memory(imageBytes!, height: 200),
                ),
              ),
            ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            child: Row(
              children: [
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: pickPhoto,
                    icon: const Icon(Icons.camera_alt),
                    label: const Text("Tomar Foto"),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: completing ? null : completeDelivery,
                    style:
                        ElevatedButton.styleFrom(backgroundColor: Colors.green),
                    child: completing
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              color: Colors.white,
                              strokeWidth: 2,
                            ),
                          )
                        : const Text("Entregado"),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}
