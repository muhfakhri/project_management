import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/task_provider.dart';
import '../../config/theme.dart';
import '../../models/task.dart';

class TaskDetailScreen extends StatefulWidget {
  final int taskId;

  const TaskDetailScreen({Key? key, required this.taskId}) : super(key: key);

  @override
  State<TaskDetailScreen> createState() => _TaskDetailScreenState();
}

class _TaskDetailScreenState extends State<TaskDetailScreen> {
  @override
  void initState() {
    super.initState();
    _loadTaskDetail();
  }

  Future<void> _loadTaskDetail() async {
    final taskProvider = Provider.of<TaskProvider>(context, listen: false);
    await taskProvider.loadTaskDetail(widget.taskId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Task Detail'),
      ),
      body: Consumer<TaskProvider>(
        builder: (context, taskProvider, child) {
          if (taskProvider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          final task = taskProvider.selectedTask;
          if (task == null) {
            return const Center(child: Text('Task not found'));
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Task Title
                Text(
                  task.cardTitle,
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
                const SizedBox(height: 16),

                // Status and Priority
                Row(
                  children: [
                    _buildStatusChip(task),
                    const SizedBox(width: 12),
                    _buildPriorityChip(task),
                  ],
                ),
                const SizedBox(height: 24),

                // Task Information
                _buildInfoCard(task),
                const SizedBox(height: 24),

                // Description
                if (task.description != null) ...[
                  Text(
                    'Description',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Text(task.description!),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],

                // Subtasks
                if (task.subtasks != null && task.subtasks!.isNotEmpty) ...[
                  _buildSubtasksList(task.subtasks!),
                  const SizedBox(height: 24),
                ],

                // Action Buttons
                _buildActionButtons(task, taskProvider),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildStatusChip(Task task) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: AppTheme.getStatusColor(task.status).withOpacity(0.1),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.circle,
            size: 12,
            color: AppTheme.getStatusColor(task.status),
          ),
          const SizedBox(width: 6),
          Text(
            task.statusLabel,
            style: TextStyle(
              color: AppTheme.getStatusColor(task.status),
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPriorityChip(Task task) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: AppTheme.getPriorityColor(task.priority).withOpacity(0.1),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Text(
        task.priorityLabel.toUpperCase(),
        style: TextStyle(
          color: AppTheme.getPriorityColor(task.priority),
          fontWeight: FontWeight.bold,
          fontSize: 12,
        ),
      ),
    );
  }

  Widget _buildInfoCard(Task task) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            if (task.projectName != null)
              _buildInfoRow(Icons.folder_outlined, 'Project', task.projectName!),
            if (task.boardName != null)
              _buildInfoRow(Icons.dashboard_outlined, 'Board', task.boardName!),
            if (task.dueDate != null)
              _buildInfoRow(Icons.calendar_today, 'Due Date', task.dueDate!.toString().split(' ')[0]),
            _buildInfoRow(Icons.access_time, 'Estimated', '${task.estimatedHours}h'),
            if (task.actualHours != null)
              _buildInfoRow(Icons.timer, 'Actual Hours', '${task.actualHours}h'),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(icon, size: 20, color: Colors.grey[600]),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              label,
              style: TextStyle(
                color: Colors.grey[600],
                fontSize: 14,
              ),
            ),
          ),
          Text(
            value,
            style: const TextStyle(
              fontWeight: FontWeight.bold,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubtasksList(List<Subtask> subtasks) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Subtasks (${subtasks.length})',
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
        ),
        const SizedBox(height: 8),
        ListView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: subtasks.length,
          itemBuilder: (context, index) {
            final subtask = subtasks[index];
            return Card(
              margin: const EdgeInsets.only(bottom: 8),
              child: ListTile(
                leading: Icon(
                  subtask.isCompleted ? Icons.check_circle : Icons.circle_outlined,
                  color: subtask.isCompleted ? AppColors.success : Colors.grey,
                ),
                title: Text(
                  subtask.subtaskTitle,
                  style: TextStyle(
                    decoration: subtask.isCompleted ? TextDecoration.lineThrough : null,
                  ),
                ),
                subtitle: subtask.description != null ? Text(subtask.description!) : null,
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildActionButtons(Task task, TaskProvider taskProvider) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Start/Pause/Complete Work Buttons
        if (task.canStartWork)
          ElevatedButton.icon(
            onPressed: taskProvider.isLoading
                ? null
                : () => _handleStartWork(task.cardId, taskProvider),
            icon: const Icon(Icons.play_arrow),
            label: const Text('Start Work'),
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
          ),
        if (task.canPauseWork) ...[
          ElevatedButton.icon(
            onPressed: taskProvider.isLoading
                ? null
                : () => _handlePauseWork(task.cardId, taskProvider),
            icon: const Icon(Icons.pause),
            label: const Text('Pause Work'),
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              backgroundColor: Colors.orange,
            ),
          ),
          const SizedBox(height: 12),
        ],
        if (task.canCompleteWork)
          ElevatedButton.icon(
            onPressed: taskProvider.isLoading
                ? null
                : () => _handleCompleteWork(task.cardId, taskProvider),
            icon: const Icon(Icons.check),
            label: const Text('Complete Work'),
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              backgroundColor: AppColors.success,
            ),
          ),
        
        // Status Update Buttons
        const SizedBox(height: 24),
        Text(
          'Update Status',
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
        ),
        const SizedBox(height: 12),
        
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            if (task.status != 'in_progress')
              _buildStatusButton(
                'In Progress',
                'in_progress',
                task.cardId,
                taskProvider,
                AppColors.inProgress,
              ),
            if (task.status != 'review' && task.status != 'todo')
              _buildStatusButton(
                'Submit for Review',
                'review',
                task.cardId,
                taskProvider,
                AppColors.review,
              ),
            if (task.status != 'done')
              _buildStatusButton(
                'Mark as Done',
                'done',
                task.cardId,
                taskProvider,
                AppColors.done,
              ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatusButton(
    String label,
    String status,
    int taskId,
    TaskProvider taskProvider,
    Color color,
  ) {
    return ElevatedButton(
      onPressed: taskProvider.isLoading
          ? null
          : () => _handleStatusUpdate(taskId, status, taskProvider),
      style: ElevatedButton.styleFrom(
        backgroundColor: color,
        foregroundColor: Colors.white,
      ),
      child: Text(label),
    );
  }

  Future<void> _handleStartWork(int taskId, TaskProvider taskProvider) async {
    final success = await taskProvider.startWork(taskId);
    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Work started successfully')),
      );
    } else if (mounted && taskProvider.errorMessage != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(taskProvider.errorMessage!),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _handlePauseWork(int taskId, TaskProvider taskProvider) async {
    final success = await taskProvider.pauseWork(taskId);
    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Work paused successfully')),
      );
    } else if (mounted && taskProvider.errorMessage != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(taskProvider.errorMessage!),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _handleCompleteWork(int taskId, TaskProvider taskProvider) async {
    final success = await taskProvider.completeWork(taskId);
    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Work completed successfully')),
      );
    } else if (mounted && taskProvider.errorMessage != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(taskProvider.errorMessage!),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _handleStatusUpdate(
    int taskId,
    String status,
    TaskProvider taskProvider,
  ) async {
    final success = await taskProvider.updateTaskStatus(taskId, status);
    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Status updated successfully')),
      );
    } else if (mounted && taskProvider.errorMessage != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(taskProvider.errorMessage!),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}
