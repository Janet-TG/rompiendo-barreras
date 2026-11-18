import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../services/package_service.dart';
import 'delivery_screen.dart';

class DeliveriesScreen extends StatefulWidget {
  const DeliveriesScreen({super.key});

  @override
  State<DeliveriesScreen> createState() => _DeliveriesScreenState();
}

class _DeliveriesScreenState extends State<DeliveriesScreen> {
  final PackageService packageService = PackageService();
  final storage = const FlutterSecureStorage();
  List<dynamic> allPackages = [];
  List<dynamic> packages = [];
  int? userId;
  bool loading = true;
  int selectedFilterIndex = 1; // 0: Todos, 1: Pendientes, 2: Entregados

  @override
  void initState() {
    super.initState();
    loadData();
  }

  Future<void> loadData() async {
    setState(() => loading = true);
    try {
      String? storedUserId = await storage.read(key: 'user_id');
      if (storedUserId != null) userId = int.tryParse(storedUserId);
      // Obtener todos los paquetes y aplicar filtro según la selección
      if (userId != null) {
        // Intentar pedir al backend solo los paquetes asignados a este usuario
        allPackages = await packageService.getPackagesForUser(userId!);
      } else {
        allPackages = [];
      }

      applyFilter();
    } catch (e) {
      debugPrint('Error loading deliveries: $e');
      packages = [];
    } finally {
      setState(() => loading = false);
    }
  }

  void applyFilter() {
    if (selectedFilterIndex == 0) {
      packages = List.from(allPackages);
    } else if (selectedFilterIndex == 1) {
      // Pendientes
      packages = allPackages.where((p) {
        final val = p['entregado'];
        return val == false ||
            val == 0 ||
            val == '0' ||
            val == 'false' ||
            (p['status'] == 'pending');
      }).toList();
    } else {
      // Entregados
      packages = allPackages.where((p) {
        final val = p['entregado'];
        return val == true ||
            val == 1 ||
            val == '1' ||
            val == 'true' ||
            (p['status'] == 'delivered');
      }).toList();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Entregas'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Column(
          children: [
            Card(
              child: Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                child: Row(
                  children: [
                    const Text('Filtrar:'),
                    const SizedBox(width: 12),
                    ToggleButtons(
                      isSelected: [
                        selectedFilterIndex == 0,
                        selectedFilterIndex == 1,
                        selectedFilterIndex == 2,
                      ],
                      onPressed: (i) {
                        setState(() {
                          selectedFilterIndex = i;
                          applyFilter();
                        });
                      },
                      borderRadius: BorderRadius.circular(8),
                      constraints:
                          const BoxConstraints(minHeight: 36, minWidth: 80),
                      children: const [
                        Padding(
                            padding: EdgeInsets.symmetric(horizontal: 8),
                            child: Text('Todos')),
                        Padding(
                            padding: EdgeInsets.symmetric(horizontal: 8),
                            child: Text('Pendientes')),
                        Padding(
                            padding: EdgeInsets.symmetric(horizontal: 8),
                            child: Text('Entregados')),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),
            Expanded(
              child: loading
                  ? const Center(child: CircularProgressIndicator())
                  : RefreshIndicator(
                      onRefresh: loadData,
                      child: packages.isEmpty
                          ? ListView(
                              children: const [
                                SizedBox(height: 60),
                                Center(child: Text('No hay entregas')),
                              ],
                            )
                          : ListView.builder(
                              itemCount: packages.length,
                              itemBuilder: (context, index) {
                                final p = packages[index];
                                return Card(
                                  child: ListTile(
                                    leading: CircleAvatar(
                                      backgroundColor: Theme.of(context)
                                          .primaryColor
                                          .withAlpha(26),
                                      child: const Icon(Icons.local_shipping,
                                          color: Colors.blue),
                                    ),
                                    title: Text('Paquete #${p['id']}'),
                                    subtitle: Text(
                                        'Dirección: ${p['direccion'] ?? 'N/D'}'),
                                    trailing:
                                        const Icon(Icons.arrow_forward_ios),
                                    onTap: () {
                                      if (userId == null) {
                                        ScaffoldMessenger.of(context)
                                            .showSnackBar(const SnackBar(
                                                content: Text(
                                                    'Usuario no identificado')));
                                        return;
                                      }
                                      Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) => DeliveryScreen(
                                              package: p, userId: userId!),
                                        ),
                                      );
                                    },
                                  ),
                                );
                              },
                            ),
                    ),
            ),
          ],
        ),
      ),
    );
  }
}
