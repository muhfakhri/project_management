class DashboardStats {
  final int completedProjects;
  final double averageCompletionTime; // in hours
  final int totalTasks;
  final int completedTasks;
  final int inProgressTasks;
  final int pendingTasks;
  final double todayWorkingHours;
  final double weekWorkingHours;
  final double monthWorkingHours;

  DashboardStats({
    required this.completedProjects,
    required this.averageCompletionTime,
    required this.totalTasks,
    required this.completedTasks,
    required this.inProgressTasks,
    required this.pendingTasks,
    required this.todayWorkingHours,
    required this.weekWorkingHours,
    required this.monthWorkingHours,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> json) {
    return DashboardStats(
      completedProjects: json['completed_projects'] ?? 0,
      averageCompletionTime: (json['average_completion_time'] ?? 0).toDouble(),
      totalTasks: json['total_tasks'] ?? 0,
      completedTasks: json['completed_tasks'] ?? 0,
      inProgressTasks: json['in_progress_tasks'] ?? 0,
      pendingTasks: json['pending_tasks'] ?? 0,
      todayWorkingHours: (json['today_working_hours'] ?? 0).toDouble(),
      weekWorkingHours: (json['week_working_hours'] ?? 0).toDouble(),
      monthWorkingHours: (json['month_working_hours'] ?? 0).toDouble(),
    );
  }

  int get completionRate {
    if (totalTasks == 0) return 0;
    return ((completedTasks / totalTasks) * 100).round();
  }
}

class AppNotification {
  final int notificationId;
  final int userId;
  final String type;
  final String title;
  final String message;
  final Map<String, dynamic>? data;
  final String? actionUrl;
  final DateTime? readAt;
  final DateTime createdAt;

  AppNotification({
    required this.notificationId,
    required this.userId,
    required this.type,
    required this.title,
    required this.message,
    this.data,
    this.actionUrl,
    this.readAt,
    required this.createdAt,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      notificationId: json['notification_id'] ?? 0,
      userId: json['user_id'] ?? 0,
      type: json['type'] ?? '',
      title: json['title'] ?? '',
      message: json['message'] ?? '',
      data: json['data'] != null ? Map<String, dynamic>.from(json['data']) : null,
      actionUrl: json['action_url'],
      readAt: json['read_at'] != null ? DateTime.tryParse(json['read_at']) : null,
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toIso8601String()),
    );
  }

  bool get isRead => readAt != null;
  bool get isUnread => readAt == null;

  String get timeAgo {
    final difference = DateTime.now().difference(createdAt);
    
    if (difference.inDays > 7) {
      return '${(difference.inDays / 7).floor()}w ago';
    } else if (difference.inDays > 0) {
      return '${difference.inDays}d ago';
    } else if (difference.inHours > 0) {
      return '${difference.inHours}h ago';
    } else if (difference.inMinutes > 0) {
      return '${difference.inMinutes}m ago';
    } else {
      return 'Just now';
    }
  }
}
