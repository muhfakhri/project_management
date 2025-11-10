import 'package:flutter/material.dart';
import '../services/blocker_service.dart';

class ReportBlockerScreen extends StatefulWidget {
  final int taskId;
  final String taskTitle;

  const ReportBlockerScreen({
    super.key,
    required this.taskId,
    required this.taskTitle,
  });

  @override
  State<ReportBlockerScreen> createState() => _ReportBlockerScreenState();
}

class _ReportBlockerScreenState extends State<ReportBlockerScreen> {
  final _formKey = GlobalKey<FormState>();
  final _reasonController = TextEditingController();
  final BlockerService _blockerService = BlockerService();
  
  String _priority = 'medium';
  bool _isSubmitting = false;

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  Future<void> _submitBlocker() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    final result = await _blockerService.reportBlocker(
      cardId: widget.taskId,
      reason: _reasonController.text.trim(),
      priority: _priority,
    );

    setState(() => _isSubmitting = false);

    if (mounted) {
      if (result['success']) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message']),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.pop(context, true); // Return true to indicate success
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message']),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Report Blocker'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Card(
                child: ListTile(
                  leading: const Icon(Icons.task),
                  title: const Text('Task'),
                  subtitle: Text(widget.taskTitle),
                ),
              ),
              const SizedBox(height: 24),
              const Text(
                'What is blocking you?',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              const Text(
                'Describe the issue or obstacle you\'re facing. Your team lead will be notified and can assign someone to help.',
                style: TextStyle(color: Colors.grey),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _reasonController,
                decoration: const InputDecoration(
                  labelText: 'Blocker Reason *',
                  hintText: 'e.g., Missing API documentation, unclear requirements, technical issue...',
                  border: OutlineInputBorder(),
                  alignLabelWithHint: true,
                ),
                maxLines: 5,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please describe the blocker';
                  }
                  if (value.trim().length < 10) {
                    return 'Please provide more details (at least 10 characters)';
                  }
                  if (value.trim().length > 1000) {
                    return 'Description is too long (max 1000 characters)';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 24),
              const Text(
                'Priority Level',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              _buildPrioritySelector(),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submitBlocker,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.red.shade600,
                    foregroundColor: Colors.white,
                  ),
                  child: _isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.report_problem),
                            SizedBox(width: 8),
                            Text('Report Blocker', style: TextStyle(fontSize: 16)),
                          ],
                        ),
                ),
              ),
              const SizedBox(height: 16),
              const Card(
                color: Colors.blue,
                child: Padding(
                  padding: EdgeInsets.all(12),
                  child: Row(
                    children: [
                      Icon(Icons.info_outline, color: Colors.white),
                      SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          'Team leads and project admins will be notified immediately',
                          style: TextStyle(color: Colors.white, fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPrioritySelector() {
    return Column(
      children: [
        _buildPriorityOption(
          'low',
          'Low',
          'Can wait, not urgent',
          Colors.green,
        ),
        _buildPriorityOption(
          'medium',
          'Medium',
          'Should be addressed soon',
          Colors.blue,
        ),
        _buildPriorityOption(
          'high',
          'High',
          'Important, affecting progress',
          Colors.orange,
        ),
        _buildPriorityOption(
          'critical',
          'Critical',
          'Urgent, completely blocked',
          Colors.red,
        ),
      ],
    );
  }

  Widget _buildPriorityOption(
    String value,
    String label,
    String description,
    Color color,
  ) {
    final isSelected = _priority == value;
    return Card(
      elevation: isSelected ? 4 : 1,
      color: isSelected ? color.withOpacity(0.1) : null,
      child: RadioListTile<String>(
        value: value,
        groupValue: _priority,
        onChanged: (val) {
          setState(() => _priority = val!);
        },
        title: Row(
          children: [
            Container(
              width: 12,
              height: 12,
              decoration: BoxDecoration(
                color: color,
                shape: BoxShape.circle,
              ),
            ),
            const SizedBox(width: 8),
            Text(
              label,
              style: TextStyle(
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
              ),
            ),
          ],
        ),
        subtitle: Text(
          description,
          style: const TextStyle(fontSize: 12),
        ),
        activeColor: color,
      ),
    );
  }
}
