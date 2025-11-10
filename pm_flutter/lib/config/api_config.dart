// API Configuration
class ApiConfig {
  // IMPORTANT: Change this to your Laravel API URL
  // For local testing: Use your computer's IP address
  // Example: 'http://192.168.1.100:8000/api'
  static const String baseUrl = 'http://127.0.0.1:8000/api';
  
  // Auth Endpoints
  static const String login = '/login';
  static const String register = '/register';
  static const String logout = '/logout';
  static const String profile = '/profile';
  static const String updateProfile = '/profile/update';
  
  // Task Endpoints
  static const String myTasks = '/tasks/my';
  static const String taskDetail = '/tasks'; // /tasks/{id}
  static const String updateTaskStatus = '/tasks'; // /tasks/{id}/status
  static const String taskHistory = '/tasks/history';
  
  // Time Tracking
  static const String startWork = '/tasks'; // /tasks/{id}/start
  static const String pauseWork = '/tasks'; // /tasks/{id}/pause
  static const String completeWork = '/tasks'; // /tasks/{id}/complete
  
  // Dashboard & Statistics
  static const String dashboardStats = '/dashboard/stats';
  
  // Notifications
  static const String notifications = '/notifications/recent';
  static const String notificationRead = '/notifications'; // /notifications/{id}/read
  static const String notificationUnreadCount = '/notifications/unread-count';
  
  // Blocker Endpoints - Help Request System
  static const String blockers = '/blockers';
  static const String myBlockers = '/blockers/my';
  static const String assignedBlockers = '/blockers/assigned';
  // /blockers/{id} - detail
  // /blockers/{id}/assign - assign helper
  // /blockers/{id}/status - update status
  // /blockers/{id}/comments - add comment
  
  // Request Headers
  static Map<String, String> headers({String? token}) {
    final Map<String, String> headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    
    return headers;
  }
  
  // Timeout durations
  static const Duration connectTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
}
