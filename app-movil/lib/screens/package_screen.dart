import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../services/package_service.dart';
import '../services/auth_service.dart';
import 'delivery_screen.dart';
import 'deliveries_screen.dart';
import 'login_screen.dart';

class PackagesScreen extends StatefulWidget {
  const PackagesScreen({super.key});

  @override
  State<PackagesScreen> createState() => _PackagesScreenState();
}

class _PackagesScreenState extends State<PackagesScreen> {
  final PackageService packageService = PackageService();
  final storage = const FlutterSecureStorage();
  List<dynamic> packages = [];
  int? userId;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    loadData();
  }

  Future<void> loadData() async {
    setState(() => loading = true);

    try {
      String? storedUserId = await storage.read(key: "user_id");
      if (storedUserId == null) {
        // Usuario no logueado, volver al login
        if (!mounted) return;
        Navigator.of(context).popUntil((route) => route.isFirst);
        return;
      }
      userId = int.parse(storedUserId);
      packages = await packageService.getPackages();
    } catch (e) {
      debugPrint('Error loading packages: $e');
      packages = [];
    } finally {
      setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: Drawer(
        child: SafeArea(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              DrawerHeader(
                decoration:
                    BoxDecoration(color: Theme.of(context).primaryColor),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    const Text('Paquetería',
                        style: TextStyle(color: Colors.white, fontSize: 20)),
                    if (userId != null)
                      Text('Usuario: $userId',
                          style: const TextStyle(color: Colors.white70)),
                  ],
                ),
              ),
              ListTile(
                leading: const Icon(Icons.list),
                title: const Text('Mis Paquetes'),
                onTap: () => Navigator.pop(context),
              ),
              ListTile(
                leading: const Icon(Icons.local_shipping),
                title: const Text('Entregas'),
                onTap: () {
                  Navigator.pop(context);
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const DeliveriesScreen()),
                  );
                },
              ),
              const Spacer(),
              const Divider(),
              ListTile(
                leading: const Icon(Icons.logout),
                title: const Text('Cerrar sesión'),
                onTap: () async {
                  final navigator = Navigator.of(context);
                  await AuthService().logout();
                  if (!mounted) return;
                  navigator.pushReplacement(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                  );
                },
              ),
            ],
          ),
        ),
      ),
      appBar: AppBar(
        title: const Text("Mis Paquetes"),
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : packages.isEmpty
              ? const Center(child: Text("No hay paquetes disponibles"))
              : RefreshIndicator(
                  onRefresh: loadData,
                  child: ListView.builder(
                    itemCount: packages.length,
                    itemBuilder: (context, index) {
                      final p = packages[index];
                      return Card(
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor:
                                Theme.of(context).primaryColor.withAlpha(26),
                            child: const Icon(Icons.local_post_office,
                                color: Colors.blue),
                          ),
                          title: Text("Paquete #${p["id"]}"),
                          subtitle: Text("Dirección: ${p["direccion"]}"),
                          trailing: const Icon(Icons.arrow_forward_ios),
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 8),
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => DeliveryScreen(
                                  package: p,
                                  userId: userId!,
                                ),
                              ),
                            );
                          },
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
