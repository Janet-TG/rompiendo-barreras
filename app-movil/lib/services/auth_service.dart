import 'dart:convert';
import 'package:flutter/foundation.dart'
    show kIsWeb, defaultTargetPlatform, TargetPlatform, debugPrint;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

class AuthService {
  // Ajusta la URL base según la plataforma:
  // - Web / desktop: usa localhost (127.0.0.1)
  // - Android emulator: usa 10.0.2.2 (emulator host)
  // - Otros: usa localhost por defecto
  String get baseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000';
    }
    if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2:8000';
    }
    return 'http://127.0.0.1:8000';
  }

  final FlutterSecureStorage storage = const FlutterSecureStorage();

  /// Inicia sesión con email y contraseña
  Future<int?> login(String email, String password) async {
    try {
      final url = Uri.parse("$baseUrl/login");
      final response = await http.post(
        url,
        headers: {"Content-Type": "application/json"},
        body: jsonEncode({
          "email": email,
          "contrasena": password,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        int userId = data["user_id"];

        await storage.write(key: "user_id", value: userId.toString());
        return userId;
      } else {
        debugPrint('Login failed: ${response.statusCode}');
        return null;
      }
    } catch (e) {
      debugPrint('Exception during login: $e');
      return null;
    }
  }

  /// Registra un nuevo usuario
  Future<bool> register(String name, String email, String password) async {
    try {
      final url = Uri.parse("$baseUrl/register");
      final response = await http.post(
        url,
        headers: {"Content-Type": "application/json"},
        body: jsonEncode({
          "nombre": name,
          "email": email,
          "contrasena": password,
        }),
      );

      // Algunos backends responden 200 o 201 al crear recursos.
      if (response.statusCode == 201 || response.statusCode == 200) {
        return true;
      }

      debugPrint('Register failed: ${response.statusCode} - ${response.body}');
      return false;
    } catch (e) {
      debugPrint('Error registering user: $e');
      return false;
    }
  }

  /// Verifica si el usuario ya inició sesión
  Future<bool> isLoggedIn() async {
    try {
      String? userId = await storage.read(key: "user_id");
      return userId != null;
    } catch (e) {
      debugPrint('Error checking login status: $e');
      return false;
    }
  }

  /// Cierra sesión
  Future<void> logout() async {
    await storage.deleteAll();
  }
}
