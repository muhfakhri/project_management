class Task {
  final int cardId;
  final String cardTitle;
  final String? description;
  final String status;
  final String priority;
  final DateTime? dueDate;
  final DateTime? startedAt;
  final DateTime? completedAt;
  final double estimatedHours;
  final double? actualHours;
  final bool needsApproval;
  final bool isApproved;
  final String? rejectionReason;
  final int createdBy;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  // Relationships
  final String? projectName;
  final String? boardName;
  final List<Subtask>? subtasks;

  Task({
    required this.cardId,
    required this.cardTitle,
    this.description,
    required this.status,
    required this.priority,
    this.dueDate,
    this.startedAt,
    this.completedAt,
    required this.estimatedHours,
    this.actualHours,
    required this.needsApproval,
    required this.isApproved,
    this.rejectionReason,
    required this.createdBy,
    required this.createdAt,
    required this.updatedAt,
    this.projectName,
    this.boardName,
    this.subtasks,
  });

  factory Task.fromJson(Map<String, dynamic> json) {
    return Task(
      cardId: json['card_id'] ?? 0,
      cardTitle: json['card_title'] ?? '',
      description: json['description'],
      status: json['status'] ?? 'todo',
      priority: json['priority'] ?? 'medium',
      dueDate: json['due_date'] != null ? DateTime.tryParse(json['due_date']) : null,
      startedAt: json['started_at'] != null ? DateTime.tryParse(json['started_at']) : null,
      completedAt: json['completed_at'] != null ? DateTime.tryParse(json['completed_at']) : null,
      estimatedHours: (json['estimated_hours'] ?? 0).toDouble(),
      actualHours: json['actual_hours'] != null ? (json['actual_hours']).toDouble() : null,
      needsApproval: json['needs_approval'] ?? false,
      isApproved: json['is_approved'] ?? false,
      rejectionReason: json['rejection_reason'],
      createdBy: json['created_by'] ?? 0,
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toIso8601String()),
      updatedAt: DateTime.parse(json['updated_at'] ?? DateTime.now().toIso8601String()),
      projectName: json['project']?['project_name'] ?? json['project_name'],
      boardName: json['board']?['board_name'] ?? json['board_name'],
      subtasks: json['subtasks'] != null
          ? (json['subtasks'] as List).map((s) => Subtask.fromJson(s)).toList()
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'card_id': cardId,
      'card_title': cardTitle,
      'description': description,
      'status': status,
      'priority': priority,
      'due_date': dueDate?.toIso8601String(),
      'started_at': startedAt?.toIso8601String(),
      'completed_at': completedAt?.toIso8601String(),
      'estimated_hours': estimatedHours,
      'actual_hours': actualHours,
      'needs_approval': needsApproval,
      'is_approved': isApproved,
      'rejection_reason': rejectionReason,
      'created_by': createdBy,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  String get statusLabel {
    switch (status.toLowerCase()) {
      case 'todo':
        return 'To Do';
      case 'in_progress':
        return 'In Progress';
      case 'review':
        return 'Review';
      case 'done':
        return 'Done';
      default:
        return status;
    }
  }

  String get priorityLabel {
    return priority[0].toUpperCase() + priority.substring(1);
  }

  bool get canStartWork => status == 'todo' && startedAt == null;
  bool get canPauseWork => status == 'in_progress' && startedAt != null;
  bool get canCompleteWork => status == 'in_progress';
  bool get isInProgress => status == 'in_progress';
  bool get isCompleted => status == 'done' && isApproved;
  bool get isWaitingApproval => status == 'review' && !isApproved;
}

class Subtask {
  final int subtaskId;
  final int cardId;
  final String subtaskTitle;
  final String? description;
  final String status;
  final bool needsApproval;
  final bool isApproved;
  final DateTime? startedAt;
  final DateTime? completedAt;
  final int? durationMinutes;

  Subtask({
    required this.subtaskId,
    required this.cardId,
    required this.subtaskTitle,
    this.description,
    required this.status,
    required this.needsApproval,
    required this.isApproved,
    this.startedAt,
    this.completedAt,
    this.durationMinutes,
  });

  factory Subtask.fromJson(Map<String, dynamic> json) {
    return Subtask(
      subtaskId: json['subtask_id'] ?? 0,
      cardId: json['card_id'] ?? 0,
      subtaskTitle: json['subtask_title'] ?? '',
      description: json['description'],
      status: json['status'] ?? 'todo',
      needsApproval: json['needs_approval'] ?? false,
      isApproved: json['is_approved'] ?? false,
      startedAt: json['started_at'] != null ? DateTime.tryParse(json['started_at']) : null,
      completedAt: json['completed_at'] != null ? DateTime.tryParse(json['completed_at']) : null,
      durationMinutes: json['duration_minutes'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'subtask_id': subtaskId,
      'card_id': cardId,
      'subtask_title': subtaskTitle,
      'description': description,
      'status': status,
      'needs_approval': needsApproval,
      'is_approved': isApproved,
      'started_at': startedAt?.toIso8601String(),
      'completed_at': completedAt?.toIso8601String(),
      'duration_minutes': durationMinutes,
    };
  }

  bool get isCompleted => status == 'done' && isApproved;
}
