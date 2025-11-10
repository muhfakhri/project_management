import 'package:flutter/material.dart';
import '../models/blocker.dart';
import '../services/blocker_service.dart';

class BlockerDetailScreen extends StatefulWidget {
  final int blockerId;

  const BlockerDetailScreen({super.key, required this.blockerId});

  @override
  State<BlockerDetailScreen> createState() => _BlockerDetailScreenState();
}

class _BlockerDetailScreenState extends State<BlockerDetailScreen> {
  final BlockerService _blockerService = BlockerService();
  final TextEditingController _commentController = TextEditingController();
  final TextEditingController _resolutionController = TextEditingController();
  
  bool _isLoading = true;
  Blocker? _blocker;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadBlocker();
  }

  @override
  void dispose() {
    _commentController.dispose();
    _resolutionController.dispose();
    super.dispose();
  }

  Future<void> _loadBlocker() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final blocker = await _blockerService.getBlockerDetail(widget.blockerId);
      setState(() {
        _blocker = blocker;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _addComment() async {
    if (_commentController.text.trim().isEmpty) return;

    final result = await _blockerService.addComment(
      blockerId: widget.blockerId,
      comment: _commentController.text.trim(),
    );

    if (mounted) {
      if (result['success']) {
        _commentController.clear();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Comment added')),
        );
        _loadBlocker();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'])),
        );
      }
    }
  }

  Future<void> _updateStatus(String status) async {
    String? resolutionNote;
    
    if (status == 'resolved') {
      // Show dialog to get resolution note
      resolutionNote = await showDialog<String>(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Resolve Blocker'),
          content: TextField(
            controller: _resolutionController,
            decoration: const InputDecoration(
              labelText: 'Resolution Note',
              hintText: 'How was this resolved?',
            ),
            maxLines: 3,
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context, _resolutionController.text.trim());
              },
              child: const Text('Resolve'),
            ),
          ],
        ),
      );
      
      if (resolutionNote == null || resolutionNote.isEmpty) return;
    }

    final result = await _blockerService.updateStatus(
      blockerId: widget.blockerId,
      status: status,
      resolutionNote: resolutionNote,
    );

    if (mounted) {
      if (result['success']) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'])),
        );
        if (status == 'resolved') {
          Navigator.pop(context);
        } else {
          _loadBlocker();
        }
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'])),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Blocker Detail'),
        actions: [
          if (_blocker != null && _blocker!.status != 'resolved')
            PopupMenuButton<String>(
              onSelected: (value) {
                if (value == 'in_progress') {
                  _updateStatus('in_progress');
                } else if (value == 'resolved') {
                  _updateStatus('resolved');
                }
              },
              itemBuilder: (context) => [
                const PopupMenuItem(
                  value: 'in_progress',
                  child: Text('Mark In Progress'),
                ),
                const PopupMenuItem(
                  value: 'resolved',
                  child: Text('Resolve'),
                ),
              ],
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 64, color: Colors.red),
                      const SizedBox(height: 16),
                      Text(_error!),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadBlocker,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : _blocker == null
                  ? const Center(child: Text('Blocker not found'))
                  : SingleChildScrollView(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildHeader(),
                          const SizedBox(height: 24),
                          _buildTaskInfo(),
                          const SizedBox(height: 24),
                          _buildReason(),
                          const SizedBox(height: 24),
                          if (_blocker!.assignee != null) _buildAssignee(),
                          if (_blocker!.resolutionNote != null) ...[
                            const SizedBox(height: 24),
                            _buildResolution(),
                          ],
                          const SizedBox(height: 24),
                          _buildComments(),
                          const SizedBox(height: 16),
                          _buildCommentInput(),
                        ],
                      ),
                    ),
    );
  }

  Widget _buildHeader() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                _buildPriorityChip(_blocker!.priority),
                const SizedBox(width: 8),
                _buildStatusChip(_blocker!.status),
                const Spacer(),
                if (_blocker!.isOverdue)
                  const Chip(
                    label: Text('OVERDUE', style: TextStyle(fontSize: 10)),
                    backgroundColor: Colors.orange,
                    padding: EdgeInsets.zero,
                  ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                const Icon(Icons.access_time, size: 16),
                const SizedBox(width: 4),
                Text('Blocked for ${_blocker!.timeBlockedHours} hours'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTaskInfo() {
    return Card(
      child: ListTile(
        leading: const Icon(Icons.task),
        title: const Text('Task'),
        subtitle: Text(_blocker!.cardTitle),
      ),
    );
  }

  Widget _buildReason() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Blocker Reason',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            const SizedBox(height: 8),
            Text(_blocker!.reason),
            const SizedBox(height: 12),
            Row(
              children: [
                CircleAvatar(
                  radius: 16,
                  child: Text(_blocker!.reporter.name[0].toUpperCase()),
                ),
                const SizedBox(width: 8),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _blocker!.reporter.name,
                      style: const TextStyle(fontWeight: FontWeight.w500),
                    ),
                    Text(
                      'Reported ${_formatDate(_blocker!.createdAt)}',
                      style: const TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAssignee() {
    return Card(
      child: ListTile(
        leading: const Icon(Icons.person),
        title: const Text('Assigned Helper'),
        subtitle: Text(_blocker!.assignee!.name),
      ),
    );
  }

  Widget _buildResolution() {
    return Card(
      color: Colors.green.shade50,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.check_circle, color: Colors.green),
                SizedBox(width: 8),
                Text(
                  'Resolution',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(_blocker!.resolutionNote!),
            const SizedBox(height: 8),
            Text(
              'Resolved ${_formatDate(_blocker!.resolvedAt!)}',
              style: const TextStyle(fontSize: 12, color: Colors.grey),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildComments() {
    if (_blocker!.comments == null || _blocker!.comments!.isEmpty) {
      return const Card(
        child: Padding(
          padding: EdgeInsets.all(16),
          child: Center(
            child: Text('No comments yet'),
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Comments',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
        ),
        const SizedBox(height: 8),
        ...(_blocker!.comments!.map((comment) => Card(
              margin: const EdgeInsets.only(bottom: 8),
              child: ListTile(
                leading: CircleAvatar(
                  child: Text(comment.user.name[0].toUpperCase()),
                ),
                title: Text(comment.user.name),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 4),
                    Text(comment.comment),
                    const SizedBox(height: 4),
                    Text(
                      _formatDate(comment.createdAt),
                      style: const TextStyle(fontSize: 10, color: Colors.grey),
                    ),
                  ],
                ),
              ),
            ))),
      ],
    );
  }

  Widget _buildCommentInput() {
    if (_blocker!.status == 'resolved') {
      return const SizedBox.shrink();
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Expanded(
              child: TextField(
                controller: _commentController,
                decoration: const InputDecoration(
                  hintText: 'Add a comment...',
                  border: OutlineInputBorder(),
                ),
                maxLines: 2,
              ),
            ),
            const SizedBox(width: 8),
            IconButton(
              icon: const Icon(Icons.send),
              onPressed: _addComment,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPriorityChip(String priority) {
    Color color;
    switch (priority) {
      case 'critical':
        color = Colors.red;
        break;
      case 'high':
        color = Colors.orange;
        break;
      case 'medium':
        color = Colors.blue;
        break;
      case 'low':
        color = Colors.green;
        break;
      default:
        color = Colors.grey;
    }

    return Chip(
      label: Text(
        priority.toUpperCase(),
        style: const TextStyle(color: Colors.white, fontSize: 10),
      ),
      backgroundColor: color,
      padding: EdgeInsets.zero,
      materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
    );
  }

  Widget _buildStatusChip(String status) {
    Color color;
    switch (status) {
      case 'reported':
        color = Colors.orange;
        break;
      case 'assigned':
        color = Colors.blue;
        break;
      case 'in_progress':
        color = Colors.purple;
        break;
      case 'resolved':
        color = Colors.green;
        break;
      default:
        color = Colors.grey;
    }

    return Chip(
      label: Text(
        status.replaceAll('_', ' ').toUpperCase(),
        style: const TextStyle(fontSize: 10),
      ),
      backgroundColor: color.withOpacity(0.2),
      padding: EdgeInsets.zero,
      materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
    );
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final difference = now.difference(date);

    if (difference.inMinutes < 60) {
      return '${difference.inMinutes}m ago';
    } else if (difference.inHours < 24) {
      return '${difference.inHours}h ago';
    } else if (difference.inDays < 7) {
      return '${difference.inDays}d ago';
    } else {
      return '${date.day}/${date.month}/${date.year}';
    }
  }
}
