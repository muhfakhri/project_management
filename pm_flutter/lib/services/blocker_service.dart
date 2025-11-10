import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config/api_config.dart';
import '../models/blocker.dart';

class BlockerService {
  final storage = const FlutterSecureStorage();

  Future<String?> _getToken() async {
    return await storage.read(key: 'auth_token');
  }

  Map<String, String> _getHeaders(String? token) {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // Get all blockers (with filters)
  Future<List<Blocker>> getBlockers({String? status, String? priority}) async {
    try {
      final token = await _getToken();
      if (token == null) throw Exception('Not authenticated');

      var url = '${ApiConfig.baseUrl}${ApiConfig.blockers}';
      final params = <String, String>{};
      if (status != null) params['status'] = status;
      if (priority != null) params['priority'] = priority;
      
      if (params.isNotEmpty) {
        url += '?${params.entries.map((e) => '${e.key}=${e.value}').join('&')}';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: _getHeaders(token),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> blockersJson = data['data'];
          return blockersJson.map((json) => Blocker.fromJson(json)).toList();
        }
      }
      throw Exception('Failed to load blockers');
    } catch (e) {
      throw Exception('Error loading blockers: $e');
    }
  }

  // Get my blockers (reported by me)
  Future<List<Blocker>> getMyBlockers() async {
    try {
      final token = await _getToken();
      if (token == null) throw Exception('Not authenticated');

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.myBlockers}'),
        headers: _getHeaders(token),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> blockersJson = data['data'];
          return blockersJson.map((json) => Blocker.fromJson(json)).toList();
        }
      }
      throw Exception('Failed to load my blockers');
    } catch (e) {
      throw Exception('Error loading my blockers: $e');
    }
  }

  // Get blockers assigned to me
  Future<List<Blocker>> getAssignedBlockers() async {
    try {
      final token = await _getToken();
      if (token == null) throw Exception('Not authenticated');

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.assignedBlockers}'),
        headers: _getHeaders(token),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> blockersJson = data['data'];
          return blockersJson.map((json) => Blocker.fromJson(json)).toList();
        }
      }
      throw Exception('Failed to load assigned blockers');
    } catch (e) {
      throw Exception('Error loading assigned blockers: $e');
    }
  }

  // Get blocker detail
  Future<Blocker> getBlockerDetail(int blockerId) async {
    try {
      final token = await _getToken();
      if (token == null) throw Exception('Not authenticated');

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.blockers}/$blockerId'),
        headers: _getHeaders(token),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          return Blocker.fromJson(data['data']);
        }
      }
      throw Exception('Failed to load blocker detail');
    } catch (e) {
      throw Exception('Error loading blocker detail: $e');
    }
  }

  // Report a new blocker
  Future<Map<String, dynamic>> reportBlocker({
    required int cardId,
    required String reason,
    required String priority,
  }) async {
    try {
      final token = await _getToken();
      if (token == null) throw Exception('Not authenticated');

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.blockers}'),
        headers: _getHeaders(token),
        body: json.encode({
          'card_id': cardId,
          'reason': reason,
          'priority': priority,
        }),
      );

      final data = json.decode(response.body);
      
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'message': data['message'],
          'blocker': Blocker.fromJson(data['data']),
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to report blocker',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Error reporting blocker: $e',
      };
    }
  }

  // Assign helper to blocker
  Future<Map<String, dynamic>> assignHelper({
    required int blockerId,
    required int userId,
  }) async {
    try {
      final token = await _getToken();
      if (token == null) throw Exception('Not authenticated');

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.blockers}/$blockerId/assign'),
        headers: _getHeaders(token),
        body: json.encode({
          'assigned_to': userId,
        }),
      );

      final data = json.decode(response.body);
      
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'message': data['message'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to assign helper',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Error assigning helper: $e',
      };
    }
  }

  // Update blocker status
  Future<Map<String, dynamic>> updateStatus({
    required int blockerId,
    required String status,
    String? resolutionNote,
  }) async {
    try {
      final token = await _getToken();
      if (token == null) throw Exception('Not authenticated');

      final body = {
        'status': status,
        if (resolutionNote != null) 'resolution_note': resolutionNote,
      };

      final response = await http.patch(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.blockers}/$blockerId/status'),
        headers: _getHeaders(token),
        body: json.encode(body),
      );

      final data = json.decode(response.body);
      
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'message': data['message'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to update status',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Error updating status: $e',
      };
    }
  }

  // Add comment to blocker
  Future<Map<String, dynamic>> addComment({
    required int blockerId,
    required String comment,
  }) async {
    try {
      final token = await _getToken();
      if (token == null) throw Exception('Not authenticated');

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.blockers}/$blockerId/comments'),
        headers: _getHeaders(token),
        body: json.encode({
          'comment': comment,
        }),
      );

      final data = json.decode(response.body);
      
      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'message': data['message'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to add comment',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Error adding comment: $e',
      };
    }
  }
}
