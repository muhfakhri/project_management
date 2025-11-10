import 'package:flutter/material.dart';
import '../models/blocker.dart';
import '../services/blocker_service.dart';
import 'blocker_detail_screen.dart';

class BlockersScreen extends StatefulWidget {
  const BlockersScreen({super.key});

  @override
  State<BlockersScreen> createState() => _BlockersScreenState();
}

class _BlockersScreenState extends State<BlockersScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final BlockerService _blockerService = BlockerService();
  
  bool _isLoading = true;
  List<Blocker> _allBlockers = [];
  List<Blocker> _myBlockers = [];
  List<Blocker> _assignedBlockers = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final results = await Future.wait([
        _blockerService.getBlockers(status: 'active'),
        _blockerService.getMyBlockers(),
        _blockerService.getAssignedBlockers(),
      ]);

      setState(() {
        _allBlockers = results[0];
        _myBlockers = results[1];
        _assignedBlockers = results[2];
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Blockers & Help Requests'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'All Active'),
            Tab(text: 'My Reports'),
            Tab(text: 'Assigned to Me'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
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
                        onPressed: _loadData,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : TabBarView(
                  controller: _tabController,
                  children: [
                    _buildBlockerList(_allBlockers),
                    _buildBlockerList(_myBlockers),
                    _buildBlockerList(_assignedBlockers),
                  ],
                ),
    );
  }

  Widget _buildBlockerList(List<Blocker> blockers) {
    if (blockers.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.check_circle_outline, size: 64, color: Colors.green),
            SizedBox(height: 16),
            Text('No active blockers', style: TextStyle(fontSize: 18)),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: blockers.length,
        itemBuilder: (context, index) {
          final blocker = blockers[index];
          return _buildBlockerCard(blocker);
        },
      ),
    );
  }

  Widget _buildBlockerCard(Blocker blocker) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => BlockerDetailScreen(blockerId: blocker.id),
            ),
          ).then((_) => _loadData());
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  _buildPriorityChip(blocker.priority),
                  const SizedBox(width: 8),
                  _buildStatusChip(blocker.status),
                  const Spacer(),
                  if (blocker.isOverdue)
                    const Icon(Icons.warning, color: Colors.orange, size: 20),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                blocker.cardTitle,
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                blocker.reason,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: Colors.black87),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  CircleAvatar(
                    radius: 12,
                    child: Text(
                      blocker.reporter.name[0].toUpperCase(),
                      style: const TextStyle(fontSize: 10),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      blocker.reporter.name,
                      style: const TextStyle(fontSize: 12),
                    ),
                  ),
                  const Icon(Icons.access_time, size: 14, color: Colors.grey),
                  const SizedBox(width: 4),
                  Text(
                    '${blocker.timeBlockedHours}h',
                    style: const TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                ],
              ),
            ],
          ),
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
}
