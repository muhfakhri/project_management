import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/dashboard.dart';
import 'auth_service.dart';

class DashboardService {
  final AuthService _authService = AuthService();

  // Get Dashboard Statistics
  Future<Map<String, dynamic>> getDashboardStats() async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.dashboardStats}'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        final stats = DashboardStats.fromJson(data['stats'] ?? data);

        return {
          'success': true,
          'stats': stats,
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to get dashboard statistics',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Refresh Dashboard Data
  Future<Map<String, dynamic>> refreshDashboard() async {
    return await getDashboardStats();
  }
}
