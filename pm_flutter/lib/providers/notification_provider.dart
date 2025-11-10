import 'package:flutter/material.dart';
import '../models/dashboard.dart';
import '../services/notification_service.dart';

class NotificationProvider extends ChangeNotifier {
  final NotificationService _notificationService = NotificationService();
  
  List<AppNotification> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = false;
  String? _errorMessage;

  List<AppNotification> get notifications => _notifications;
  int get unreadCount => _unreadCount;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  // Get unread notifications
  List<AppNotification> get unreadNotifications {
    return _notifications.where((n) => n.isUnread).toList();
  }

  // Load Notifications
  Future<void> loadNotifications({int limit = 20}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _notificationService.getNotifications(limit: limit);

    if (result['success']) {
      _notifications = result['notifications'];
      _unreadCount = _notifications.where((n) => n.isUnread).length;
    } else {
      _errorMessage = result['message'];
    }

    _isLoading = false;
    notifyListeners();
  }

  // Mark Notification as Read
  Future<bool> markAsRead(int notificationId) async {
    final result = await _notificationService.markAsRead(notificationId);

    if (result['success']) {
      // Reload notifications to get updated data
      await loadNotifications();
      return true;
    } else {
      _errorMessage = result['message'];
      notifyListeners();
      return false;
    }
  }

  // Mark All as Read
  Future<bool> markAllAsRead() async {
    _isLoading = true;
    notifyListeners();

    final result = await _notificationService.markAllAsRead();

    if (result['success']) {
      // Reload notifications to get updated data
      await loadNotifications();
      return true;
    } else {
      _errorMessage = result['message'];
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Get Unread Count
  Future<void> loadUnreadCount() async {
    final result = await _notificationService.getUnreadCount();

    if (result['success']) {
      _unreadCount = result['count'];
      notifyListeners();
    }
  }

  // Clear error
  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}
