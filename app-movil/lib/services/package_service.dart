import 'dart:convert';
import 'package:flutter/foundation.dart'
    show kIsWeb, defaultTargetPlatform, TargetPlatform, debugPrint;
import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';
import 'package:image_picker/image_picker.dart';

class PackageService {
  String get baseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000';
    }
    if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2:8000';
    }
    return 'http://127.0.0.1:8000';
  }

  /// Obtiene la lista de paquetes desde el backend
  Future<List<dynamic>> getPackages() async {
    try {
      final url = Uri.parse("$baseUrl/packages");
      final response = await http.get(url);

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        debugPrint('Failed to fetch packages: ${response.statusCode}');
        return [];
      }
    } catch (e) {
      debugPrint('Exception fetching packages: $e');
      return [];
    }
  }

  /// Intenta obtener paquetes filtrados por usuario directamente desde el backend.
  ///
  /// El método prueba varios nombres de parámetro y endpoints comunes hasta
  /// encontrar uno que devuelva datos (200). Si ninguno funciona, devuelve
  /// la lista completa usando `getPackages()` como fallback.
  Future<List<dynamic>> getPackagesForUser(int userId) async {
    final paramNames = [
      'usu_id',
      'user_id',
      'repartidor_id',
      'assigned_to',
      'assigned_to_id'
    ];
    final endpoints = ['/packages', '/deliveries'];

    for (final endpoint in endpoints) {
      for (final param in paramNames) {
        try {
          final uri = Uri.parse('$baseUrl$endpoint')
              .replace(queryParameters: {param: userId.toString()});
          final resp = await http.get(uri);
          if (resp.statusCode == 200) {
            final decoded = jsonDecode(resp.body);
            if (decoded is List) {
              return decoded;
            }
          }
        } catch (e) {
          debugPrint('Attempt failed for $endpoint?$param: $e');
          // continue trying other combinations
        }
      }
    }

    // Fallback: obtener todos y dejar que el cliente filtre (existing behavior)
    return await getPackages();
  }

  /// Marca un paquete como entregado
  /// Completa una entrega subiendo la foto (multipart) y la ubicación.
  /// `photo` puede ser un `XFile` (recomendado) o una ruta de archivo en el dispositivo.
  Future<bool> completeDelivery({
    required int packageId,
    required int userId,
    required dynamic photo,
    required double lat,
    required double lng,
  }) async {
    try {
      final uri = Uri.parse("$baseUrl/deliveries/$packageId/complete");

      final request = http.MultipartRequest('POST', uri);
      request.fields['usu_id'] = userId.toString();
      request.fields['latitud'] = lat.toString();
      request.fields['longitud'] = lng.toString();

      // Adjuntar el archivo de foto en modo compatible web y móvil
      if (kIsWeb) {
        if (photo is XFile) {
          final bytes = await photo.readAsBytes();
          final multipartFile = http.MultipartFile.fromBytes(
            'photo',
            bytes,
            filename: photo.name.isNotEmpty ? photo.name : 'photo.jpg',
            contentType: MediaType('image', 'jpeg'),
          );
          request.files.add(multipartFile);
        } else if (photo is List<int>) {
          final multipartFile = http.MultipartFile.fromBytes(
            'photo',
            photo,
            filename: 'photo.jpg',
            contentType: MediaType('image', 'jpeg'),
          );
          request.files.add(multipartFile);
        }
      } else {
        // En plataformas móviles/desktop intentamos usar la ruta si se proporciona
        if (photo is String) {
          final multipartFile = await http.MultipartFile.fromPath(
              'photo', photo,
              contentType: MediaType('image', 'jpeg'));
          request.files.add(multipartFile);
        } else if (photo is XFile) {
          final multipartFile = await http.MultipartFile.fromPath(
              'photo', photo.path,
              contentType: MediaType('image', 'jpeg'));
          request.files.add(multipartFile);
        }
      }

      final streamed = await request.send();
      final response = await http.Response.fromStream(streamed);

      if (response.statusCode == 200 || response.statusCode == 201) {
        return true;
      }

      debugPrint(
          'Failed to complete delivery: ${response.statusCode} - ${response.body}');
      return false;
    } catch (e) {
      debugPrint('Exception completing delivery: $e');
      return false;
    }
  }
}
