import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/task.dart';
import 'auth_service.dart';

class TaskService {
  final AuthService _authService = AuthService();

  // Get My Tasks
  Future<Map<String, dynamic>> getMyTasks() async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.myTasks}'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        final tasks = (data['tasks'] ?? data['data'] ?? [])
            .map<Task>((json) => Task.fromJson(json))
            .toList();

        return {
          'success': true,
          'tasks': tasks,
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to get tasks',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Get Task Detail
  Future<Map<String, dynamic>> getTaskDetail(int taskId) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.taskDetail}/$taskId'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        final task = Task.fromJson(data['task'] ?? data);

        return {
          'success': true,
          'task': task,
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to get task detail',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Update Task Status
  Future<Map<String, dynamic>> updateTaskStatus(int taskId, String status) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.updateTaskStatus}/$taskId/status'),
        headers: ApiConfig.headers(token: token),
        body: json.encode({'status': status}),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': data['message'] ?? 'Status updated successfully',
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
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Start Work on Task
  Future<Map<String, dynamic>> startWork(int taskId) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.startWork}/$taskId/start'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': data['message'] ?? 'Work started successfully',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to start work',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Pause Work on Task
  Future<Map<String, dynamic>> pauseWork(int taskId) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.pauseWork}/$taskId/pause'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': data['message'] ?? 'Work paused successfully',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to pause work',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Complete Work on Task
  Future<Map<String, dynamic>> completeWork(int taskId) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.completeWork}/$taskId/complete'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': data['message'] ?? 'Work completed successfully',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to complete work',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Get Task History
  Future<Map<String, dynamic>> getTaskHistory() async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.taskHistory}'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        final tasks = (data['tasks'] ?? data['data'] ?? [])
            .map<Task>((json) => Task.fromJson(json))
            .toList();

        return {
          'success': true,
          'tasks': tasks,
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to get task history',
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
