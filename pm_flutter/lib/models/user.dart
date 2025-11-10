class User {
  final int userId;
  final String username;
  final String? fullName;
  final String email;
  final String? profilePicture;
  final String role;
  final DateTime? createdAt;

  User({
    required this.userId,
    required this.username,
    this.fullName,
    required this.email,
    this.profilePicture,
    required this.role,
    this.createdAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      userId: json['user_id'] ?? 0,
      username: json['username'] ?? '',
      fullName: json['full_name'],
      email: json['email'] ?? '',
      profilePicture: json['profile_picture'],
      role: json['role'] ?? 'Developer',
      createdAt: json['created_at'] != null 
          ? DateTime.tryParse(json['created_at']) 
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'user_id': userId,
      'username': username,
      'full_name': fullName,
      'email': email,
      'profile_picture': profilePicture,
      'role': role,
      'created_at': createdAt?.toIso8601String(),
    };
  }

  String get displayName => fullName?.isNotEmpty == true ? fullName! : username;
  
  String get initials {
    if (fullName?.isNotEmpty == true) {
      final parts = fullName!.split(' ');
      if (parts.length >= 2) {
        return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
      }
      return fullName!.substring(0, 2).toUpperCase();
    }
    return username.substring(0, 2).toUpperCase();
  }
}
