import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config/api_config.dart';
import '../models/user.dart';

class AuthService {
  static const _storage = FlutterSecureStorage();
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';

  // Login
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.login}'),
        headers: ApiConfig.headers(),
        body: json.encode({
          'email': email,
          'password': password,
        }),
      ).timeout(ApiConfig.connectTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        // Save token and user data
        if (data['token'] != null) {
          await _storage.write(key: _tokenKey, value: data['token']);
        }
        if (data['user'] != null) {
          await _storage.write(key: _userKey, value: json.encode(data['user']));
        }

        return {
          'success': true,
          'user': User.fromJson(data['user']),
          'token': data['token'],
          'message': data['message'] ?? 'Login successful',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Login failed',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Register
  Future<Map<String, dynamic>> register({
    required String username,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? fullName,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.register}'),
        headers: ApiConfig.headers(),
        body: json.encode({
          'username': username,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'full_name': fullName,
        }),
      ).timeout(ApiConfig.connectTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 201 || response.statusCode == 200) {
        // Auto login after register
        if (data['token'] != null) {
          await _storage.write(key: _tokenKey, value: data['token']);
        }
        if (data['user'] != null) {
          await _storage.write(key: _userKey, value: json.encode(data['user']));
        }

        return {
          'success': true,
          'user': User.fromJson(data['user']),
          'token': data['token'],
          'message': data['message'] ?? 'Registration successful',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Registration failed',
          'errors': data['errors'],
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Logout
  Future<void> logout() async {
    try {
      final token = await getToken();
      if (token != null) {
        await http.post(
          Uri.parse('${ApiConfig.baseUrl}${ApiConfig.logout}'),
          headers: ApiConfig.headers(token: token),
        ).timeout(ApiConfig.connectTimeout);
      }
    } catch (e) {
      // Ignore errors on logout
    } finally {
      await _storage.delete(key: _tokenKey);
      await _storage.delete(key: _userKey);
    }
  }

  // Get stored token
  Future<String?> getToken() async {
    return await _storage.read(key: _tokenKey);
  }

  // Get stored user
  Future<User?> getUser() async {
    final userString = await _storage.read(key: _userKey);
    if (userString != null) {
      return User.fromJson(json.decode(userString));
    }
    return null;
  }

  // Check if user is logged in
  Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  // Get Profile
  Future<Map<String, dynamic>> getProfile() async {
    try {
      final token = await getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.profile}'),
        headers: ApiConfig.headers(token: token),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        final user = User.fromJson(data['user'] ?? data);
        // Update stored user data
        await _storage.write(key: _userKey, value: json.encode(user.toJson()));
        
        return {
          'success': true,
          'user': user,
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to get profile',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Connection error: ${e.toString()}',
      };
    }
  }

  // Update Profile
  Future<Map<String, dynamic>> updateProfile({
    String? fullName,
    String? email,
    String? currentPassword,
    String? newPassword,
  }) async {
    try {
      final token = await getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final body = <String, dynamic>{};
      if (fullName != null) body['full_name'] = fullName;
      if (email != null) body['email'] = email;
      if (currentPassword != null) body['current_password'] = currentPassword;
      if (newPassword != null) body['new_password'] = newPassword;

      final response = await http.put(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.updateProfile}'),
        headers: ApiConfig.headers(token: token),
        body: json.encode(body),
      ).timeout(ApiConfig.receiveTimeout);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        final user = User.fromJson(data['user'] ?? data);
        // Update stored user data
        await _storage.write(key: _userKey, value: json.encode(user.toJson()));
        
        return {
          'success': true,
          'user': user,
          'message': data['message'] ?? 'Profile updated successfully',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to update profile',
          'errors': data['errors'],
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
