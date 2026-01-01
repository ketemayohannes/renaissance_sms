-- Renaissance SMS MySQL Structure Export
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (`id` int primary key auto_increment not null, `migration` VARCHAR(255) not null, `batch` INT not null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (`id` int primary key auto_increment not null, `name` VARCHAR(255) not null, `email` VARCHAR(255) not null, `email_verified_at` datetime, `password` VARCHAR(255) not null, `remember_token` VARCHAR(255), `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (`email` VARCHAR(255) not null, `token` VARCHAR(255) not null, `created_at` datetime, primary key (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (`id` VARCHAR(255) not null, `user_id` INT, `ip_address` VARCHAR(255), `user_agent` text, `payload` text not null, `last_activity` INT not null, primary key (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (`key` VARCHAR(255) not null, `value` text not null, `expiration` INT not null, primary key (`key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (`key` VARCHAR(255) not null, `owner` VARCHAR(255) not null, `expiration` INT not null, primary key (`key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (`id` int primary key auto_increment not null, `queue` VARCHAR(255) not null, `payload` text not null, `attempts` INT not null, `reserved_at` INT, `available_at` INT not null, `created_at` INT not null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (`id` VARCHAR(255) not null, `name` VARCHAR(255) not null, `total_jobs` INT not null, `pending_jobs` INT not null, `failed_jobs` INT not null, `failed_job_ids` text not null, `options` text, `cancelled_at` INT, `created_at` INT not null, `finished_at` INT, primary key (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (`id` int primary key auto_increment not null, `uuid` VARCHAR(255) not null, `connection` text not null, `queue` text not null, `payload` text not null, `exception` text not null, `failed_at` datetime not null default CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (`id` int primary key auto_increment not null, `name` VARCHAR(255) not null, `guard_name` VARCHAR(255) not null, `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (`id` int primary key auto_increment not null, `name` VARCHAR(255) not null, `guard_name` VARCHAR(255) not null, `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (`permission_id` INT not null, `model_type` VARCHAR(255) not null, `model_id` INT not null, foreign key(`permission_id`) references `permissions`(`id`) on delete cascade, primary key (`permission_id`, `model_id`, `model_type`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (`role_id` INT not null, `model_type` VARCHAR(255) not null, `model_id` INT not null, foreign key(`role_id`) references `roles`(`id`) on delete cascade, primary key (`role_id`, `model_id`, `model_type`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (`permission_id` INT not null, `role_id` INT not null, foreign key(`permission_id`) references `permissions`(`id`) on delete cascade, foreign key(`role_id`) references `roles`(`id`) on delete cascade, primary key (`permission_id`, `role_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `divisions`;
CREATE TABLE `divisions` (`id` int primary key auto_increment not null, `name` VARCHAR(255) not null, `code` VARCHAR(255) not null, `description` text, `sort_order` INT not null default '0', `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `academic_years`;
CREATE TABLE `academic_years` (`id` int primary key auto_increment not null, `name` VARCHAR(255) not null, `start_date` date not null, `end_date` date not null, `is_active` tinyint(1) not null default '0', `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `grade_levels`;
CREATE TABLE `grade_levels` (`id` int primary key auto_increment not null, `division_id` INT not null, `name` VARCHAR(255) not null, `code` VARCHAR(255) not null, `sort_order` INT not null default '0', `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime, foreign key(`division_id`) references `divisions`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (`id` int primary key auto_increment not null, `grade_level_id` INT not null, `academic_year_id` INT not null, `name` VARCHAR(255) not null, `capacity` INT not null default '30', `homeroom_teacher_id` INT, `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime, foreign key(`grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade, foreign key(`homeroom_teacher_id`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (`id` int primary key auto_increment not null, `name` VARCHAR(255) not null, `code` VARCHAR(255) not null, `description` text, `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime, `is_elective` tinyint(1) not null default '0', `sort_order` INT not null default '0') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `grade_level_subjects`;
CREATE TABLE `grade_level_subjects` (`id` int primary key auto_increment not null, `grade_level_id` INT not null, `subject_id` INT not null, `academic_year_id` INT not null, `is_required` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime, `sort_order` INT not null default '0', foreign key(`grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`subject_id`) references `subjects`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (`id` int primary key auto_increment not null, `user_id` INT not null, `student_id` VARCHAR(255) not null, `first_name` VARCHAR(255) not null, `middle_name` VARCHAR(255), `last_name` VARCHAR(255) not null, `gender` VARCHAR(255) not null, `date_of_birth` date not null, `admission_number` VARCHAR(255) not null, `admission_date` date not null, `photo` VARCHAR(255), `address` text, `phone` VARCHAR(255), `email` VARCHAR(255), `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime, `father_name` VARCHAR(255) not null, `grandfather_name` VARCHAR(255) not null, `nationality` VARCHAR(255) not null default 'Ethiopian', `language_spoken` VARCHAR(255), `subcity` VARCHAR(255), `woreda` VARCHAR(255), `house_number` VARCHAR(255), `birth_country` VARCHAR(255), `birth_city` VARCHAR(255), `deleted_at` datetime, foreign key(`user_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_enrollments`;
CREATE TABLE `student_enrollments` (`id` int primary key auto_increment not null, `student_id` INT not null, `section_id` INT not null, `academic_year_id` INT not null, `enrollment_date` date not null, `status` VARCHAR(255) not null default 'active', `created_at` datetime, `updated_at` datetime, `end_date` date, `roll_number` INT, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`section_id`) references `sections`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `teacher_assignments`;
CREATE TABLE `teacher_assignments` (`id` int primary key auto_increment not null, `teacher_id` INT not null, `section_id` INT not null, `subject_id` INT not null, `academic_year_id` INT not null, `created_at` datetime, `updated_at` datetime, foreign key(`teacher_id`) references `users`(`id`) on delete cascade, foreign key(`section_id`) references `sections`(`id`) on delete cascade, foreign key(`subject_id`) references `subjects`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `conduct_grades`;
CREATE TABLE `conduct_grades` (`id` int primary key auto_increment not null, `grade` VARCHAR(255) not null, `description` VARCHAR(255) not null, `sort_order` INT not null default '0', `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `term_results`;
CREATE TABLE `term_results` (`id` int primary key auto_increment not null, `student_id` INT not null, `subject_id` INT not null, `term_id` INT not null, `section_id` INT not null, `total_marks` DECIMAL(19,4) not null, `percentage` DECIMAL(19,4) not null, `grade` VARCHAR(255), `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`subject_id`) references `subjects`(`id`) on delete cascade, foreign key(`term_id`) references `terms`(`id`) on delete cascade, foreign key(`section_id`) references `sections`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `semester_results`;
CREATE TABLE `semester_results` (`id` int primary key auto_increment not null, `student_id` INT not null, `subject_id` INT not null, `academic_year_id` INT not null, `semester_number` INT not null, `total_marks` DECIMAL(19,4) not null, `percentage` DECIMAL(19,4) not null, `grade` VARCHAR(255), `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`subject_id`) references `subjects`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `yearly_results`;
CREATE TABLE `yearly_results` (`id` int primary key auto_increment not null, `student_id` INT not null, `subject_id` INT not null, `academic_year_id` INT not null, `total_marks` DECIMAL(19,4) not null, `percentage` DECIMAL(19,4) not null, `grade` VARCHAR(255), `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`subject_id`) references `subjects`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_overall_performance`;
CREATE TABLE `student_overall_performance` (`id` int primary key auto_increment not null, `student_id` INT not null, `academic_year_id` INT not null, `period_type` VARCHAR(255) not null, `period_number` INT, `total_marks` DECIMAL(19,4) not null, `average_percentage` DECIMAL(19,4) not null, `total_subjects` INT not null, `conduct_grade_id` INT, `teacher_remarks` text, `principal_remarks` text, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade, foreign key(`conduct_grade_id`) references `conduct_grades`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `rankings`;
CREATE TABLE `rankings` (`id` int primary key auto_increment not null, `student_id` INT not null, `academic_year_id` INT not null, `period_type` VARCHAR(255) not null, `period_number` INT, `rank_type` VARCHAR(255) not null, `section_id` INT, `grade_level_id` INT, `division_id` INT, `rank` INT not null, `total_students` INT not null, `average_percentage` DECIMAL(19,4) not null, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade, foreign key(`section_id`) references `sections`(`id`) on delete cascade, foreign key(`grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`division_id`) references `divisions`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `report_cards`;
CREATE TABLE `report_cards` (`id` int primary key auto_increment not null, `student_id` INT not null, `academic_year_id` INT not null, `report_type` VARCHAR(255) not null, `report_number` INT, `file_path` VARCHAR(255) not null, `generated_at` datetime not null, `generated_by` INT not null, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade, foreign key(`generated_by`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `fee_structures`;
CREATE TABLE `fee_structures` (`id` int primary key auto_increment not null, `name` VARCHAR(255) not null, `description` text, `grade_level_id` INT, `academic_year_id` INT not null, `amount` DECIMAL(19,4) not null, `frequency` VARCHAR(255) not null default 'yearly', `due_date` date, `is_mandatory` tinyint(1) not null default '1', `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime, foreign key(`grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_fees`;
CREATE TABLE `student_fees` (`id` int primary key auto_increment not null, `student_id` INT not null, `fee_structure_id` INT not null, `academic_year_id` INT not null, `total_amount` DECIMAL(19,4) not null, `paid_amount` DECIMAL(19,4) not null default '0', `discount_amount` DECIMAL(19,4) not null default '0', `balance` DECIMAL(19,4) not null, `status` VARCHAR(255) not null default 'unpaid', `due_date` date, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`fee_structure_id`) references `fee_structures`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (`id` int primary key auto_increment not null, `student_fee_id` INT not null, `receipt_number` VARCHAR(255) not null, `amount` DECIMAL(19,4) not null, `payment_date` date not null, `payment_method` VARCHAR(255) not null, `transaction_reference` VARCHAR(255), `remarks` text, `received_by` INT not null, `created_at` datetime, `updated_at` datetime, foreign key(`student_fee_id`) references `student_fees`(`id`) on delete cascade, foreign key(`received_by`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (`id` int primary key auto_increment not null, `user_id` INT not null, `employee_id` VARCHAR(255) not null, `first_name` VARCHAR(255) not null, `middle_name` VARCHAR(255), `last_name` VARCHAR(255) not null, `gender` VARCHAR(255) not null, `date_of_birth` date not null, `phone` VARCHAR(255) not null, `email` VARCHAR(255) not null, `address` text, `designation` VARCHAR(255) not null, `department` VARCHAR(255), `joining_date` date not null, `leaving_date` date, `basic_salary` DECIMAL(19,4) not null, `photo` VARCHAR(255), `employment_type` VARCHAR(255) not null default 'full_time', `status` VARCHAR(255) not null default 'active', `created_at` datetime, `updated_at` datetime, `grandfather_name` VARCHAR(255), `marital_status` VARCHAR(255), `region` VARCHAR(255), `zone` VARCHAR(255), `woreda` VARCHAR(255), `national_id` VARCHAR(255), `tin` VARCHAR(255), `pension_number` VARCHAR(255), `staff_category` VARCHAR(255) not null default 'administrative', `emergency_contact_name` VARCHAR(255), `emergency_contact_phone` VARCHAR(255), `bank_name` VARCHAR(255), `account_number` VARCHAR(255), `teacher_rank` VARCHAR(255), `qualification_level` VARCHAR(255), `specialization` VARCHAR(255), `periods_per_week` INT, foreign key(`user_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payrolls`;
CREATE TABLE `payrolls` (`id` int primary key auto_increment not null, `employee_id` INT not null, `month` INT not null, `year` INT not null, `basic_salary` DECIMAL(19,4) not null, `allowances` DECIMAL(19,4) not null default '0', `deductions` DECIMAL(19,4) not null default '0', `gross_salary` DECIMAL(19,4) not null, `net_salary` DECIMAL(19,4) not null, `status` VARCHAR(255) not null default 'pending', `payment_date` date, `remarks` text, `processed_by` INT, `created_at` datetime, `updated_at` datetime, foreign key(`employee_id`) references `employees`(`id`) on delete cascade, foreign key(`processed_by`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `allowances`;
CREATE TABLE `allowances` (`id` int primary key auto_increment not null, `payroll_id` INT not null, `name` VARCHAR(255) not null, `amount` DECIMAL(19,4) not null, `created_at` datetime, `updated_at` datetime, foreign key(`payroll_id`) references `payrolls`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `deductions`;
CREATE TABLE `deductions` (`id` int primary key auto_increment not null, `payroll_id` INT not null, `name` VARCHAR(255) not null, `amount` DECIMAL(19,4) not null, `created_at` datetime, `updated_at` datetime, foreign key(`payroll_id`) references `payrolls`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `employee_attendance`;
CREATE TABLE `employee_attendance` (`id` int primary key auto_increment not null, `employee_id` INT not null, `attendance_date` date not null, `check_in` time, `check_out` time, `status` VARCHAR(255) not null default 'present', `remarks` text, `created_at` datetime, `updated_at` datetime, foreign key(`employee_id`) references `employees`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `leave_requests`;
CREATE TABLE `leave_requests` (`id` int primary key auto_increment not null, `employee_id` INT not null, `leave_type` VARCHAR(255) not null, `start_date` date not null, `end_date` date not null, `total_days` INT not null, `reason` text not null, `status` VARCHAR(255) not null default 'pending', `approved_by` INT, `approval_remarks` text, `created_at` datetime, `updated_at` datetime, foreign key(`employee_id`) references `employees`(`id`) on delete cascade, foreign key(`approved_by`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (`id` int primary key auto_increment not null, `sender_id` INT not null, `title` VARCHAR(255) not null, `message` text not null, `type` VARCHAR(255) not null default 'system', `recipient_type` VARCHAR(255) not null default 'individual', `recipient_ids` text, `recipient_group` VARCHAR(255), `status` VARCHAR(255) not null default 'draft', `sent_at` datetime, `created_at` datetime, `updated_at` datetime, foreign key(`sender_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notification_recipients`;
CREATE TABLE `notification_recipients` (`id` int primary key auto_increment not null, `notification_id` INT not null, `recipient_id` INT not null, `is_read` tinyint(1) not null default '0', `read_at` datetime, `created_at` datetime, `updated_at` datetime, foreign key(`notification_id`) references `notifications`(`id`) on delete cascade, foreign key(`recipient_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices` (`id` int primary key auto_increment not null, `title` VARCHAR(255) not null, `content` text not null, `posted_by` INT not null, `publish_date` date not null, `expiry_date` date, `target_audience` VARCHAR(255) not null, `is_active` tinyint(1) not null default '1', `attachment` VARCHAR(255), `created_at` datetime, `updated_at` datetime, foreign key(`posted_by`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_attendance`;
CREATE TABLE `student_attendance` (`id` int primary key auto_increment not null, `student_id` INT not null, `section_id` INT not null, `attendance_date` date not null, `status` VARCHAR(255) not null default 'present', `remarks` text, `marked_by` INT not null, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`section_id`) references `sections`(`id`) on delete cascade, foreign key(`marked_by`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `conversations`;
CREATE TABLE `conversations` (`id` int primary key auto_increment not null, `type` VARCHAR(255) not null default 'private', `name` VARCHAR(255), `created_by` INT, `created_at` datetime, `updated_at` datetime, foreign key(`created_by`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `conversation_participants`;
CREATE TABLE `conversation_participants` (`id` int primary key auto_increment not null, `conversation_id` INT not null, `user_id` INT not null, `joined_at` datetime not null default CURRENT_TIMESTAMP, `is_admin` tinyint(1) not null default '0', `created_at` datetime, `updated_at` datetime, foreign key(`conversation_id`) references `conversations`(`id`) on delete cascade, foreign key(`user_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (`id` int primary key auto_increment not null, `conversation_id` INT not null, `sender_id` INT not null, `body` text, `attachment` VARCHAR(255), `attachment_type` VARCHAR(255), `read_at` datetime, `created_at` datetime, `updated_at` datetime, foreign key(`conversation_id`) references `conversations`(`id`) on delete cascade, foreign key(`sender_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `message_reads`;
CREATE TABLE `message_reads` (`id` int primary key auto_increment not null, `message_id` INT not null, `user_id` INT not null, `read_at` datetime not null default CURRENT_TIMESTAMP, `created_at` datetime, `updated_at` datetime, foreign key(`message_id`) references `messages`(`id`) on delete cascade, foreign key(`user_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `library_books`;
CREATE TABLE `library_books` (`id` int primary key auto_increment not null, `title` VARCHAR(255) not null, `author` VARCHAR(255) not null, `isbn` VARCHAR(255), `publisher` VARCHAR(255), `category` VARCHAR(255), `type` VARCHAR(255) not null default 'physical', `quantity` INT not null default '1', `available_copies` INT not null default '1', `shelf_location` VARCHAR(255), `file_path` VARCHAR(255), `file_format` VARCHAR(255), `file_size` INT, `cover_image` VARCHAR(255), `description` text, `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `library_borrowings`;
CREATE TABLE `library_borrowings` (`id` int primary key auto_increment not null, `book_id` INT not null, `user_id` INT not null, `issued_date` date not null, `due_date` date not null, `returned_date` date, `fine_amount` DECIMAL(19,4) not null default '0', `status` VARCHAR(255) not null default 'issued', `remarks` text, `issued_by` INT not null, `created_at` datetime, `updated_at` datetime, foreign key(`book_id`) references `library_books`(`id`) on delete cascade, foreign key(`user_id`) references `users`(`id`) on delete cascade, foreign key(`issued_by`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `promotion_rules`;
CREATE TABLE `promotion_rules` (`id` int primary key auto_increment not null, `from_grade_level_id` INT not null, `to_grade_level_id` INT not null, `academic_year_id` INT not null, `min_average` DECIMAL(19,4) not null default '50', `min_attendance_percent` DECIMAL(19,4) not null default '75', `max_failed_subjects` INT not null default '0', `description` text, `created_at` datetime, `updated_at` datetime, foreign key(`from_grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`to_grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_promotions`;
CREATE TABLE `student_promotions` (`id` int primary key auto_increment not null, `student_id` INT not null, `from_academic_year_id` INT not null, `to_academic_year_id` INT not null, `from_grade_level_id` INT not null, `to_grade_level_id` INT not null, `status` VARCHAR(255) not null default 'promoted', `remarks` text, `processed_by` INT not null, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`from_academic_year_id`) references `academic_years`(`id`) on delete cascade, foreign key(`to_academic_year_id`) references `academic_years`(`id`) on delete cascade, foreign key(`from_grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`to_grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`processed_by`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `transcript_configurations`;
CREATE TABLE `transcript_configurations` (`id` int primary key auto_increment not null, `division_id` INT not null, `start_grade_level_id` INT not null, `end_grade_level_id` INT not null, `description` text, `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime, foreign key(`division_id`) references `divisions`(`id`) on delete cascade, foreign key(`start_grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`end_grade_level_id`) references `grade_levels`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `transcripts`;
CREATE TABLE `transcripts` (`id` int primary key auto_increment not null, `student_id` INT not null, `type` VARCHAR(255) not null default 'division_completion', `academic_years_covered` text not null, `grade_levels_covered` text not null, `file_path` VARCHAR(255) not null, `generated_at` datetime not null, `generated_by` INT not null, `remarks` text, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`generated_by`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `transcript_details`;
CREATE TABLE `transcript_details` (`id` int primary key auto_increment not null, `transcript_id` INT not null, `academic_year_id` INT not null, `grade_level_id` INT not null, `subject_id` INT not null, `yearly_average` DECIMAL(19,4) not null, `grade` VARCHAR(255), `remarks` text, `created_at` datetime, `updated_at` datetime, foreign key(`transcript_id`) references `transcripts`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade, foreign key(`grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`subject_id`) references `subjects`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_medical_info`;
CREATE TABLE `student_medical_info` (`id` int primary key auto_increment not null, `student_id` INT not null, `blood_group` VARCHAR(255), `medical_issues` text, `current_medication` text, `allergies` text, `emergency_contact` VARCHAR(255), `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_transportation`;
CREATE TABLE `student_transportation` (`id` int primary key auto_increment not null, `student_id` INT not null, `driver_id` VARCHAR(255), `driver_photo` VARCHAR(255), `driver_first_name` VARCHAR(255), `driver_father_name` VARCHAR(255), `driver_grandfather_name` VARCHAR(255), `license_number` VARCHAR(255), `vehicle_plate` VARCHAR(255), `route` VARCHAR(255), `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_siblings`;
CREATE TABLE `student_siblings` (`id` int primary key auto_increment not null, `student_id` INT not null, `sibling_id` INT not null, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`sibling_id`) references `students`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `terms`;
CREATE TABLE `terms` (`id` int primary key auto_increment not null, `academic_year_id` INT not null, `name` VARCHAR(255) not null, `type` VARCHAR(255) not null, `term_number` INT not null, `start_date` date not null, `end_date` date not null, `created_at` datetime, `updated_at` datetime, `parent_term_id` INT, `is_grading_open` tinyint(1) not null default '1', `is_master_grading_open` tinyint(1) not null default '1', foreign key(`academic_year_id`) references academic_years(`id`) on delete cascade on update no action, foreign key(`parent_term_id`) references `terms`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `assessment_types`;
CREATE TABLE `assessment_types` (`id` int primary key auto_increment not null, `name` VARCHAR(255) not null, `code` VARCHAR(255) not null, `description` text, `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `grade_components`;
CREATE TABLE `grade_components` (`id` int primary key auto_increment not null, `grade_level_subject_id` INT, `term_id` INT not null, `name` VARCHAR(255) not null, `weight` DECIMAL(19,4) not null, `max_score` DECIMAL(19,4) not null default ('100'), `sort_order` INT not null default ('0'), `created_at` datetime, `updated_at` datetime, `assessment_type_id` INT, `academic_year_id` INT, `grade_level_id` INT, `subject_id` INT, `order` INT not null default ('0'), `is_active` tinyint(1) not null default ('1'), foreign key(`subject_id`) references subjects(`id`) on delete cascade on update no action, foreign key(`grade_level_id`) references grade_levels(`id`) on delete cascade on update no action, foreign key(`academic_year_id`) references academic_years(`id`) on delete cascade on update no action, foreign key(`assessment_type_id`) references assessment_types(`id`) on delete cascade on update no action, foreign key(`grade_level_subject_id`) references grade_level_subjects(`id`) on delete cascade on update no action, foreign key(`term_id`) references terms(`id`) on delete cascade on update no action) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `assessment_templates`;
CREATE TABLE `assessment_templates` (`id` int primary key auto_increment not null, `academic_year_id` INT not null, `term_id` INT, `assessment_type_id` INT not null, `name` VARCHAR(255) not null, `weight` DECIMAL(19,4) not null, `max_score` DECIMAL(19,4) not null, `order` INT not null default '0', `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade, foreign key(`term_id`) references `terms`(`id`) on delete cascade, foreign key(`assessment_type_id`) references `assessment_types`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `assessment_template_assignments`;
CREATE TABLE `assessment_template_assignments` (`id` int primary key auto_increment not null, `assessment_template_id` INT not null, `grade_level_id` INT not null, `subject_id` INT not null, `created_at` datetime, `updated_at` datetime, foreign key(`assessment_template_id`) references `assessment_templates`(`id`) on delete cascade, foreign key(`grade_level_id`) references `grade_levels`(`id`) on delete cascade, foreign key(`subject_id`) references `subjects`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_marks`;
CREATE TABLE `student_marks` (`id` int primary key auto_increment not null, `student_id` INT not null, `assessment_template_id` INT not null, `section_id` INT not null, `teacher_id` INT not null, `score` DECIMAL(19,4) not null, `remarks` text, `created_at` datetime, `updated_at` datetime, `academic_year_id` INT not null, `term_id` INT not null, `subject_id` INT not null, foreign key(`subject_id`) references subjects(`id`) on delete cascade on update no action, foreign key(`term_id`) references terms(`id`) on delete cascade on update no action, foreign key(`teacher_id`) references users(`id`) on delete cascade on update no action, foreign key(`section_id`) references sections(`id`) on delete cascade on update no action, foreign key(`student_id`) references students(`id`) on delete cascade on update no action, foreign key(`academic_year_id`) references academic_years(`id`) on delete cascade on update no action, foreign key(`assessment_template_id`) references `assessment_templates`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_electives`;
CREATE TABLE `student_electives` (`id` int primary key auto_increment not null, `student_id` INT not null, `subject_id` INT not null, `academic_year_id` INT not null, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`subject_id`) references `subjects`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `report_card_settings`;
CREATE TABLE `report_card_settings` (`id` int primary key auto_increment not null, `school_name` VARCHAR(255) not null default 'Renaissance School', `school_address` text, `website` VARCHAR(255), `telephone` VARCHAR(255), `logo_path` VARCHAR(255), `template_config` text, `created_at` datetime, `updated_at` datetime, `email` VARCHAR(255), `po_box` VARCHAR(255)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_term_records`;
CREATE TABLE `student_term_records` (`id` int primary key auto_increment not null, `student_id` INT not null, `term_id` INT not null, `academic_year_id` INT not null, `total_attendance_days` INT, `days_absent` INT not null default '0', `conduct_grade` VARCHAR(255), `homeroom_teacher_comment` text, `principal_comment` text, `behavior_traits` text, `created_at` datetime, `updated_at` datetime, `total_score` DECIMAL(19,4), `average_score` DECIMAL(19,4), `rank` INT, `rank_out_of` INT, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`term_id`) references `terms`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `academic_report_settings`;
CREATE TABLE `academic_report_settings` (`id` int primary key auto_increment not null, `roster_logo_path` VARCHAR(255), `school_name` VARCHAR(255), `display_options` text, `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (`id` int primary key auto_increment not null, `user_id` INT, `event` VARCHAR(255) not null, `auditable_type` VARCHAR(255), `auditable_id` INT, `old_values` text, `new_values` text, `url` VARCHAR(255), `ip_address` VARCHAR(255), `user_agent` VARCHAR(255), `created_at` datetime, `updated_at` datetime, foreign key(`user_id`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `system_counters`;
CREATE TABLE `system_counters` (`id` int primary key auto_increment not null, `key` VARCHAR(255) not null, `value` INT not null default '0', `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `export_requests`;
CREATE TABLE `export_requests` (`id` int primary key auto_increment not null, `user_id` INT not null, `type` VARCHAR(255) not null, `status` VARCHAR(255) not null default 'pending', `params` text, `file_path` VARCHAR(255), `error_message` text, `completed_at` datetime, `created_at` datetime, `updated_at` datetime, foreign key(`user_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `disciplinary_records`;
CREATE TABLE `disciplinary_records` (`id` int primary key auto_increment not null, `student_id` INT not null, `academic_year_id` INT not null, `incident_date` date not null, `incident_type` VARCHAR(255) not null, `severity` VARCHAR(255) not null, `description` text not null, `action_taken` text, `reported_by` INT not null, `handled_by` INT, `status` VARCHAR(255) not null default 'reported', `resolution_date` date, `resolution_notes` text, `notify_parent` tinyint(1) not null default '0', `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`academic_year_id`) references `academic_years`(`id`) on delete cascade, foreign key(`reported_by`) references `users`(`id`) on delete cascade, foreign key(`handled_by`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_status_history`;
CREATE TABLE `student_status_history` (`id` int primary key auto_increment not null, `student_id` INT not null, `old_status` VARCHAR(255), `new_status` VARCHAR(255) not null, `reason` VARCHAR(255), `notes` text, `effective_date` date, `changed_by` INT not null, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade, foreign key(`changed_by`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_guardians`;
CREATE TABLE `student_guardians` (`id` int primary key auto_increment not null, `student_id` INT not null, `guardian_type` VARCHAR(255) not null, `photo` VARCHAR(255), `first_name` VARCHAR(255) not null, `father_name` VARCHAR(255) not null, `grandfather_name` VARCHAR(255) not null, `phone` VARCHAR(255) not null, `email` VARCHAR(255), `relationship` VARCHAR(255), `created_at` datetime, `updated_at` datetime, `user_id` INT, `is_emergency_contact` tinyint(1) not null default '0', `communication_preferences` text, `address` text, foreign key(`student_id`) references students(`id`) on delete cascade on update no action, foreign key(`user_id`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_documents`;
CREATE TABLE `student_documents` (`id` int primary key auto_increment not null, `student_id` INT not null, `title` VARCHAR(255) not null, `document_type` VARCHAR(255) not null default 'other', `file_path` VARCHAR(255) not null, `notes` text, `created_at` datetime, `updated_at` datetime, foreign key(`student_id`) references `students`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
