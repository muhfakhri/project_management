import 'package:flutter/material.dart';
import '../models/task.dart';
import '../services/task_service.dart';

class TaskProvider extends ChangeNotifier {
  final TaskService _taskService = TaskService();
  
  List<Task> _tasks = [];
  Task? _selectedTask;
  bool _isLoading = false;
  String? _errorMessage;

  List<Task> get tasks => _tasks;
  Task? get selectedTask => _selectedTask;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  // Get tasks by status
  List<Task> getTasksByStatus(String status) {
    return _tasks.where((task) => task.status == status).toList();
  }

  // Get My Tasks
  Future<void> loadMyTasks() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _taskService.getMyTasks();

    if (result['success']) {
      _tasks = result['tasks'];
    } else {
      _errorMessage = result['message'];
    }

    _isLoading = false;
    notifyListeners();
  }

  // Get Task Detail
  Future<void> loadTaskDetail(int taskId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _taskService.getTaskDetail(taskId);

    if (result['success']) {
      _selectedTask = result['task'];
    } else {
      _errorMessage = result['message'];
    }

    _isLoading = false;
    notifyListeners();
  }

  // Update Task Status
  Future<bool> updateTaskStatus(int taskId, String status) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _taskService.updateTaskStatus(taskId, status);

    if (result['success']) {
      // Refresh task detail
      await loadTaskDetail(taskId);
      // Refresh task list
      await loadMyTasks();
      _isLoading = false;
      notifyListeners();
      return true;
    } else {
      _errorMessage = result['message'];
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Start Work
  Future<bool> startWork(int taskId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _taskService.startWork(taskId);

    if (result['success']) {
      await loadTaskDetail(taskId);
      _isLoading = false;
      notifyListeners();
      return true;
    } else {
      _errorMessage = result['message'];
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Pause Work
  Future<bool> pauseWork(int taskId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _taskService.pauseWork(taskId);

    if (result['success']) {
      await loadTaskDetail(taskId);
      _isLoading = false;
      notifyListeners();
      return true;
    } else {
      _errorMessage = result['message'];
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Complete Work
  Future<bool> completeWork(int taskId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _taskService.completeWork(taskId);

    if (result['success']) {
      await loadTaskDetail(taskId);
      await loadMyTasks();
      _isLoading = false;
      notifyListeners();
      return true;
    } else {
      _errorMessage = result['message'];
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Clear selected task
  void clearSelectedTask() {
    _selectedTask = null;
    notifyListeners();
  }

  // Clear error
  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}
