class Blocker {
  final int id;
  final int cardId;
  final String cardTitle;
  final String reason;
  final String priority;
  final String status;
  final BlockerUser reporter;
  final BlockerUser? assignee;
  final String? resolutionNote;
  final int timeBlockedHours;
  final bool isOverdue;
  final DateTime createdAt;
  final DateTime? resolvedAt;
  final List<BlockerComment>? comments;

  Blocker({
    required this.id,
    required this.cardId,
    required this.cardTitle,
    required this.reason,
    required this.priority,
    required this.status,
    required this.reporter,
    this.assignee,
    this.resolutionNote,
    required this.timeBlockedHours,
    required this.isOverdue,
    required this.createdAt,
    this.resolvedAt,
    this.comments,
  });

  factory Blocker.fromJson(Map<String, dynamic> json) {
    return Blocker(
      id: json['id'] as int,
      cardId: json['card_id'] as int,
      cardTitle: json['card_title'] as String,
      reason: json['reason'] as String,
      priority: json['priority'] as String,
      status: json['status'] as String,
      reporter: BlockerUser.fromJson(json['reporter'] as Map<String, dynamic>),
      assignee: json['assignee'] != null
          ? BlockerUser.fromJson(json['assignee'] as Map<String, dynamic>)
          : null,
      resolutionNote: json['resolution_note'] as String?,
      timeBlockedHours: json['time_blocked_hours'] as int,
      isOverdue: json['is_overdue'] as bool,
      createdAt: DateTime.parse(json['created_at'] as String),
      resolvedAt: json['resolved_at'] != null
          ? DateTime.parse(json['resolved_at'] as String)
          : null,
      comments: json['comments'] != null
          ? (json['comments'] as List)
              .map((c) => BlockerComment.fromJson(c as Map<String, dynamic>))
              .toList()
          : null,
    );
  }

  String get priorityLabel {
    switch (priority) {
      case 'critical':
        return 'Critical';
      case 'high':
        return 'High';
      case 'medium':
        return 'Medium';
      case 'low':
        return 'Low';
      default:
        return priority;
    }
  }

  String get statusLabel {
    switch (status) {
      case 'reported':
        return 'Reported';
      case 'assigned':
        return 'Assigned';
      case 'in_progress':
        return 'In Progress';
      case 'resolved':
        return 'Resolved';
      default:
        return status;
    }
  }
}

class BlockerUser {
  final int id;
  final String name;
  final String? avatar;

  BlockerUser({
    required this.id,
    required this.name,
    this.avatar,
  });

  factory BlockerUser.fromJson(Map<String, dynamic> json) {
    return BlockerUser(
      id: json['id'] as int,
      name: json['name'] as String,
      avatar: json['avatar'] as String?,
    );
  }
}

class BlockerComment {
  final int id;
  final BlockerUser user;
  final String comment;
  final DateTime createdAt;

  BlockerComment({
    required this.id,
    required this.user,
    required this.comment,
    required this.createdAt,
  });

  factory BlockerComment.fromJson(Map<String, dynamic> json) {
    return BlockerComment(
      id: json['id'] as int,
      user: BlockerUser.fromJson(json['user'] as Map<String, dynamic>),
      comment: json['comment'] as String,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}
