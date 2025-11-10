import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/dashboard.dart';
import 'auth_service.dart';

class NotificationService {
  final AuthService _authService = AuthService();

  // Get Recent Notifications
  Future<Map<String, dynamic>> getNotifications({int limit = 20}) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final uri = Uri.parse('${ApiConfig.baseUrl}${ApiConfig.notifications}')
          .replace(queryParameters: {'limit': limit.toString()});

      final response = await http.get(
        uri,
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        final notifications = (data['notifications'] ?? data['data'] ?? [])
            .map<AppNotification>((json) => AppNotification.fromJson(json))
            .toList();

        return {
          'success': true,
          'notifications': notifications,
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to get notifications',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Mark Notification as Read
  Future<Map<String, dynamic>> markAsRead(int notificationId) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.notificationRead}/$notificationId/read'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': data['message'] ?? 'Notification marked as read',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to mark as read',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Mark All Notifications as Read
  Future<Map<String, dynamic>> markAllAsRead() async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/notifications/mark-all-read'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': data['message'] ?? 'All notifications marked as read',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to mark all as read',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Get Unread Notification Count
  Future<Map<String, dynamic>> getUnreadCount() async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}/notifications/unread-count'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'count': data['count'] ?? 0,
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to get unread count',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }
}
