-- DBIS schema: CREATE TABLE statements from uploaded schema text
-- No database/schema prefix is included.
-- No INSERT data is included.
-- Table order is dependency-safe where explicit FOREIGN KEY REFERENCES clauses were present.

SET FOREIGN_KEY_CHECKS=0;

-- Table: `auth_groups_users`
CREATE TABLE `auth_groups_users` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `user_id` int(11) unsigned NOT NULL,
 `group` varchar(255) NOT NULL,
 `created_at` datetime NOT NULL,
 PRIMARY KEY (`id`),
 KEY `auth_groups_users_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Table: `auth_identities`
CREATE TABLE `auth_identities` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `user_id` int(11) unsigned NOT NULL,
 `type` varchar(255) NOT NULL,
 `name` varchar(255) DEFAULT NULL,
 `secret` varchar(255) NOT NULL,
 `secret2` varchar(255) DEFAULT NULL,
 `expires` datetime DEFAULT NULL,
 `extra` text DEFAULT NULL,
 `force_reset` tinyint(1) NOT NULL DEFAULT 0,
 `last_used_at` datetime DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `type_secret` (`type`,`secret`),
 KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Table: `auth_logins`
CREATE TABLE `auth_logins` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `ip_address` varchar(255) NOT NULL,
 `user_agent` varchar(255) DEFAULT NULL,
 `id_type` varchar(255) NOT NULL,
 `identifier` varchar(255) NOT NULL,
 `user_id` int(11) unsigned DEFAULT NULL,
 `date` datetime NOT NULL,
 `success` tinyint(1) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `id_type_identifier` (`id_type`,`identifier`),
 KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6863 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Table: `auth_permissions_users`
CREATE TABLE `auth_permissions_users` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `user_id` int(11) unsigned NOT NULL,
 `permission` varchar(255) NOT NULL,
 `created_at` datetime NOT NULL,
 PRIMARY KEY (`id`),
 KEY `auth_permissions_users_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Table: `auth_remember_tokens`
CREATE TABLE `auth_remember_tokens` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `selector` varchar(255) NOT NULL,
 `hashedValidator` varchar(255) NOT NULL,
 `user_id` int(11) unsigned NOT NULL,
 `expires` datetime NOT NULL,
 `created_at` datetime NOT NULL,
 `updated_at` datetime NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `selector` (`selector`),
 KEY `auth_remember_tokens_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Table: `auth_token_logins`
CREATE TABLE `auth_token_logins` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `ip_address` varchar(255) NOT NULL,
 `user_agent` varchar(255) DEFAULT NULL,
 `id_type` varchar(255) NOT NULL,
 `identifier` varchar(255) NOT NULL,
 `user_id` int(11) unsigned DEFAULT NULL,
 `date` datetime NOT NULL,
 `success` tinyint(1) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `id_type_identifier` (`id_type`,`identifier`),
 KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Table: `clients`
CREATE TABLE `clients` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `mrn` bigint(20) DEFAULT NULL,
 `internal_mrn` varchar(255) DEFAULT NULL,
 `first_name` varchar(255) NOT NULL,
 `last_name` varchar(255) DEFAULT NULL,
 `status` tinyint(4) NOT NULL DEFAULT 1,
 `description` text DEFAULT NULL,
 `created_by` int(11) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `deleted_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_abc_items`
CREATE TABLE `client_abc_items` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `category` varchar(50) NOT NULL,
 `value` text NOT NULL,
 `order` int(11) NOT NULL DEFAULT 0,
 `created_by` int(10) unsigned NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(10) unsigned DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_education`
CREATE TABLE `client_education` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `educational_setting` enum('Home','School','Both') DEFAULT NULL,
 `school_name` varchar(255) DEFAULT NULL,
 `one_to_one_support` tinyint(1) DEFAULT 0,
 `school_type` enum('Mainstream','Special Education') DEFAULT NULL,
 `date_enrolled` date DEFAULT NULL,
 `attendance_schedule` text DEFAULT NULL,
 `home_program` tinyint(1) DEFAULT 0,
 `weekly_hours` decimal(5,2) DEFAULT NULL,
 `home_program_start_date` date DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
 PRIMARY KEY (`id`),
 UNIQUE KEY `uq_education_client` (`client_id`),
 KEY `ix_education_client_id` (`client_id`),
 CONSTRAINT `fk_education_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_effective_teaching_procedures`
CREATE TABLE `client_effective_teaching_procedures` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `competing_positive_reinforcers` text DEFAULT NULL,
 `mix_and_vary_tasks` text DEFAULT NULL,
 `errorless_teaching_procedures` text DEFAULT NULL,
 `easy_to_hard_percentage` text DEFAULT NULL,
 `easy_responses_fade_start` text DEFAULT NULL,
 `schedule_of_reinforcement` text DEFAULT NULL,
 `general_comment` text DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `idx_client_id` (`client_id`),
 CONSTRAINT `fk_client_effective_teaching_procedures_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_graph_phase_line`
CREATE TABLE `client_graph_phase_line` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `p_date` date NOT NULL,
 `client_id` int(11) NOT NULL,
 `graph_type` varchar(20) NOT NULL,
 `p_key` text NOT NULL,
 `created_by` int(11) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_graph_target_month`
CREATE TABLE `client_graph_target_month` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `t_date` date NOT NULL,
 `graph_type` varchar(255) NOT NULL,
 `created_by` int(11) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_guardians`
CREATE TABLE `client_guardians` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `name` varchar(255) DEFAULT NULL,
 `address` text DEFAULT NULL,
 `telephone` varchar(50) DEFAULT NULL,
 `email` varchar(100) DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
 PRIMARY KEY (`id`),
 KEY `ix_guardians_client_id` (`client_id`),
 CONSTRAINT `fk_guardians_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_household_members`
CREATE TABLE `client_household_members` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `name` varchar(255) NOT NULL,
 `age` int(11) DEFAULT NULL,
 `relationship` varchar(100) DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 PRIMARY KEY (`id`),
 KEY `ix_household_client_id` (`client_id`),
 CONSTRAINT `fk_household_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_information`
CREATE TABLE `client_information` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `date_of_birth` date DEFAULT NULL,
 `address` text DEFAULT NULL,
 `primary_diagnosis` varchar(255) DEFAULT NULL,
 `date_primary_diagnosis` date DEFAULT NULL,
 `age_primary_diagnosis` int(11) DEFAULT NULL,
 `secondary_diagnosis` varchar(255) DEFAULT NULL,
 `date_secondary_diagnosis` date DEFAULT NULL,
 `age_secondary_diagnosis` int(11) DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
 PRIMARY KEY (`id`),
 UNIQUE KEY `uq_client_information_client` (`client_id`),
 KEY `ix_client_information_client_id` (`client_id`),
 CONSTRAINT `fk_client_information_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_mands_default_reinforcers`
CREATE TABLE `client_mands_default_reinforcers` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `name` text NOT NULL,
 `order` int(11) NOT NULL DEFAULT 0,
 `created_by` int(10) unsigned NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(10) unsigned DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_mands_reinforcer`
CREATE TABLE `client_mands_reinforcer` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `reinforcer_name` varchar(255) NOT NULL,
 `introduced_at` date NOT NULL,
 `vocal_sign` varchar(255) DEFAULT NULL,
 `description` text DEFAULT NULL,
 `created_by` int(11) DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `uniq_client_reinforcer` (`client_id`,`reinforcer_name`),
 KEY `idx_client` (`client_id`),
 KEY `idx_reinforcer_name` (`reinforcer_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9826 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_mands_reinforcer_media`
CREATE TABLE `client_mands_reinforcer_media` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_reinforcer_id` int(11) NOT NULL,
 `media_type` enum('image','video') NOT NULL,
 `media_path` varchar(500) NOT NULL,
 `created_by` int(11) DEFAULT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`),
 KEY `idx_client_reinforcer` (`client_reinforcer_id`),
 CONSTRAINT `fk_client_reinforcer_media` FOREIGN KEY (`client_reinforcer_id`) REFERENCES `client_mands_reinforcer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_medical_info`
CREATE TABLE `client_medical_info` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `prescribing_doctor` varchar(255) DEFAULT NULL,
 `previous_medications` text DEFAULT NULL,
 `medical_conditions` text DEFAULT NULL,
 `allergies` text DEFAULT NULL,
 `current_medical_provider` varchar(255) DEFAULT NULL,
 `sleeping_habits` text DEFAULT NULL,
 `eating_habits` text DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
 PRIMARY KEY (`id`),
 UNIQUE KEY `uq_medical_info_client` (`client_id`),
 KEY `ix_medical_info_client_id` (`client_id`),
 CONSTRAINT `fk_medical_info_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_medications`
CREATE TABLE `client_medications` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `category` enum('Medication','Supplement') NOT NULL,
 `name` varchar(255) NOT NULL,
 `dosage` varchar(100) DEFAULT NULL,
 `frequency` varchar(100) DEFAULT NULL,
 `prescribed_for` text DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 PRIMARY KEY (`id`),
 KEY `ix_medications_client_id` (`client_id`),
 KEY `ix_medications_category` (`category`),
 CONSTRAINT `fk_medications_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_other_diagnoses`
CREATE TABLE `client_other_diagnoses` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `diagnosis_name` varchar(255) NOT NULL,
 `diagnosis_date` date DEFAULT NULL,
 `diagnosis_age` int(11) DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 PRIMARY KEY (`id`),
 KEY `ix_other_diagnoses_client_id` (`client_id`),
 CONSTRAINT `fk_other_diagnoses_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_probe_rules`
CREATE TABLE `client_probe_rules` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_probe_set_id` int(11) NOT NULL,
 `phase_id` int(11) NOT NULL,
 `rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rules`)),
 PRIMARY KEY (`id`),
 KEY `client_probe_set_id` (`client_probe_set_id`),
 KEY `phase_id` (`phase_id`)
) ENGINE=InnoDB AUTO_INCREMENT=916 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_probe_set`
CREATE TABLE `client_probe_set` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `goal_id` int(11) NOT NULL,
 `probe_set_id` int(11) NOT NULL,
 `combination_id` int(11) NOT NULL,
 `inputs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`inputs`)),
 `is_active` tinyint(1) DEFAULT 1,
 `start_date` datetime NOT NULL DEFAULT current_timestamp(),
 `end_date` datetime DEFAULT NULL,
 `created_by` int(11) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `client_id` (`client_id`),
 KEY `goal_id` (`goal_id`),
 KEY `probe_set_id` (`probe_set_id`),
 KEY `combination_id` (`combination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=286 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_program_change`
CREATE TABLE `client_program_change` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `alert_id` int(11) NOT NULL,
 `client_id` int(11) NOT NULL,
 `domain_id` int(11) NOT NULL,
 `goal_id` int(11) NOT NULL,
 `target_id` int(11) NOT NULL,
 `collection_id` int(11) NOT NULL,
 `processed_data_id` int(11) NOT NULL,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `client_probe_set_id` int(11) NOT NULL,
 `consecutive_criteria` int(11) DEFAULT NULL,
 `other_ant` text DEFAULT NULL,
 `other_con` text DEFAULT NULL,
 `incorrect_response` text DEFAULT NULL,
 `behavioral_variables` text DEFAULT NULL,
 `description` text DEFAULT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `created_by` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `alert_id` (`alert_id`),
 KEY `client_id` (`client_id`),
 KEY `domain_id` (`domain_id`),
 KEY `goal_id` (`goal_id`),
 KEY `target_id` (`target_id`),
 KEY `collection_id` (`collection_id`),
 KEY `processed_data_id` (`processed_data_id`),
 KEY `session_id` (`session_id`),
 KEY `client_probe_set_id` (`client_probe_set_id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_program_change_alert`
CREATE TABLE `client_program_change_alert` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `domain_id` int(11) NOT NULL,
 `goal_id` int(11) NOT NULL,
 `target_id` int(11) NOT NULL,
 `collection_id` int(11) NOT NULL,
 `processed_data_id` int(11) NOT NULL,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `client_probe_set_id` int(11) NOT NULL,
 `is_alert_handled` tinyint(1) NOT NULL DEFAULT 0,
 `is_change_made` tinyint(1) NOT NULL DEFAULT 0,
 `comments` text DEFAULT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `created_by` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `client_id` (`client_id`),
 KEY `domain_id` (`domain_id`),
 KEY `goal_id` (`goal_id`),
 KEY `target_id` (`target_id`),
 KEY `collection_id` (`collection_id`),
 KEY `processed_data_id` (`processed_data_id`),
 KEY `session_id` (`session_id`),
 KEY `client_probe_set_id` (`client_probe_set_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2823 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_program_change_ant`
CREATE TABLE `client_program_change_ant` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `prog_ch_id` int(11) NOT NULL,
 `ant_id` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `prog_ch_id` (`prog_ch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_program_change_con`
CREATE TABLE `client_program_change_con` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `prog_ch_id` int(11) NOT NULL,
 `con_id` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `prog_ch_id` (`prog_ch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_program_domains`
CREATE TABLE `client_program_domains` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `description` text DEFAULT NULL,
 `domain_code` varchar(50) DEFAULT NULL,
 `mp_domain_id` int(11) NOT NULL DEFAULT 0,
 `client_id` int(11) NOT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_program_goals`
CREATE TABLE `client_program_goals` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `domain_id` int(11) DEFAULT NULL,
 `name` varchar(255) DEFAULT NULL,
 `description` text DEFAULT NULL,
 `goal_code` varchar(50) DEFAULT NULL,
 `mp_goal_id` int(11) NOT NULL DEFAULT 0,
 `client_id` int(11) NOT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=293 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_program_targets`
CREATE TABLE `client_program_targets` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `goal_id` int(11) DEFAULT NULL,
 `name` varchar(255) DEFAULT NULL,
 `description` text DEFAULT NULL,
 `mp_target_id` int(11) NOT NULL DEFAULT 0,
 `client_id` int(11) NOT NULL,
 `on_hold` tinyint(1) NOT NULL DEFAULT 0,
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `idx_cpt_client_goal_on_hold` (`client_id`,`goal_id`,`on_hold`)
) ENGINE=InnoDB AUTO_INCREMENT=4063 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_program_targets_doi`
CREATE TABLE `client_program_targets_doi` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `processed_data_id` int(11) NOT NULL,
 `collection_id` int(11) NOT NULL,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `client_id` int(11) NOT NULL,
 `domain_id` int(11) NOT NULL,
 `goal_id` int(11) NOT NULL,
 `target_id` int(11) NOT NULL,
 `client_probe_set_id` int(11) NOT NULL,
 `doi_value` int(11) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `created_by` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `client_id` (`client_id`),
 KEY `domain_id` (`domain_id`),
 KEY `goal_id` (`goal_id`),
 KEY `target_id` (`target_id`),
 KEY `collection_id` (`collection_id`),
 KEY `processed_data_id` (`processed_data_id`),
 KEY `session_id` (`session_id`),
 KEY `client_probe_set_id` (`client_probe_set_id`)
) ENGINE=InnoDB AUTO_INCREMENT=961 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_program_targets_overrides`
CREATE TABLE `client_program_targets_overrides` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `domain_id` int(11) NOT NULL,
 `goal_id` int(11) NOT NULL,
 `target_id` int(11) NOT NULL,
 `probe_set_id` int(11) NOT NULL,
 `phase_id` int(11) DEFAULT NULL,
 `consecutive_criteria` int(11) DEFAULT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `created_by` int(11) NOT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `client_id` (`client_id`),
 KEY `target_id` (`target_id`),
 KEY `probe_set_id` (`probe_set_id`),
 KEY `phase_id` (`phase_id`),
 KEY `domain_id` (`domain_id`),
 KEY `goal_id` (`goal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_program_targets_retained`
CREATE TABLE `client_program_targets_retained` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `processed_data_id` int(11) NOT NULL,
 `collection_id` int(11) NOT NULL,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `client_id` int(11) NOT NULL,
 `domain_id` int(11) NOT NULL,
 `goal_id` int(11) NOT NULL,
 `target_id` int(11) NOT NULL,
 `client_probe_set_id` int(11) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `created_by` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `client_id` (`client_id`),
 KEY `domain_id` (`domain_id`),
 KEY `goal_id` (`goal_id`),
 KEY `target_id` (`target_id`),
 KEY `collection_id` (`collection_id`),
 KEY `processed_data_id` (`processed_data_id`),
 KEY `session_id` (`session_id`),
 KEY `client_probe_set_id` (`client_probe_set_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3195 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_target_stimulus_chains`
CREATE TABLE `client_target_stimulus_chains` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `target_id` int(11) NOT NULL,
 `method` enum('forward','backward','total_task') NOT NULL,
 `rule_override` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rule_override`)),
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_target_stimulus_steps`
CREATE TABLE `client_target_stimulus_steps` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `target_id` int(11) NOT NULL,
 `step_number` int(11) NOT NULL,
 `sd_text` text NOT NULL,
 `c_text` text DEFAULT NULL,
 `response_text` text DEFAULT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `created_by` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=166 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_target_stimulus_step_mastery`
CREATE TABLE `client_target_stimulus_step_mastery` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `target_id` int(11) NOT NULL,
 `step_id` int(11) NOT NULL,
 `method` enum('baseline','forward','backward') NOT NULL,
 `collection_id` int(11) NOT NULL,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `created_by` int(11) NOT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_target_stimulus_step_sessions_data`
CREATE TABLE `client_target_stimulus_step_sessions_data` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `collection_id` int(11) DEFAULT NULL,
 `client_id` int(11) NOT NULL,
 `target_id` int(11) NOT NULL,
 `step_id` int(11) NOT NULL,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `phase_id` int(11) NOT NULL,
 `method` enum('forward','backward','total_task','baseline') NOT NULL,
 `attempt_no` int(11) DEFAULT NULL,
 `input_result` varchar(10) NOT NULL,
 `is_mastered_snapshot` tinyint(1) DEFAULT 0,
 `created_by` int(11) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `target_step_date` (`target_id`,`step_id`,`session_date`),
 KEY `client_session_idx` (`client_id`,`session_id`),
 KEY `target_phase_method` (`target_id`,`phase_id`,`method`),
 KEY `collection_id_idx` (`collection_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5719 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `client_user_mapping`
CREATE TABLE `client_user_mapping` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `client_id` int(11) NOT NULL,
 `is_default` int(11) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=222 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_sessions`
CREATE TABLE `daily_sessions` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `instructor_id` int(11) NOT NULL,
 `supervisor_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `start_time` time NOT NULL,
 `end_time` time DEFAULT NULL,
 `manual_duration` int(11) DEFAULT NULL,
 `session_rating` int(11) DEFAULT NULL,
 `instructor_comments` text DEFAULT NULL,
 `supervisor_comments` text DEFAULT NULL,
 `comments` text DEFAULT NULL,
 `note` text DEFAULT NULL,
 `status` int(11) NOT NULL,
 `flag` int(11) DEFAULT 0,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `created_by` int(11) NOT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1906 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_sessions_mands_duration`
CREATE TABLE `daily_sessions_mands_duration` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `client_id` int(11) NOT NULL,
 `start_time` time NOT NULL,
 `end_time` time DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6353 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_sessions_pb_duration`
CREATE TABLE `daily_sessions_pb_duration` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `client_id` int(11) NOT NULL,
 `start_time` time NOT NULL,
 `end_time` time DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1189 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_sessions_pb_records`
CREATE TABLE `daily_sessions_pb_records` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `pb_timer_id` int(11) NOT NULL,
 `client_id` int(11) NOT NULL,
 `session_id` int(11) NOT NULL,
 `session_date` date DEFAULT NULL,
 `antecedent` varchar(255) DEFAULT NULL,
 `behavior` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`behavior`)),
 `consequence` varchar(255) DEFAULT NULL,
 `abc_comments` text DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
 `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_sessions_teaching_duration`
CREATE TABLE `daily_sessions_teaching_duration` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `client_id` int(11) NOT NULL,
 `start_time` time NOT NULL,
 `end_time` time DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3439 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_session_data_collection`
CREATE TABLE `daily_session_data_collection` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `client_id` int(11) NOT NULL,
 `domain_id` int(11) NOT NULL,
 `goal_id` int(11) NOT NULL,
 `target_id` int(11) NOT NULL,
 `client_probe_set_id` int(11) NOT NULL,
 `current_phase_id` int(11) NOT NULL,
 `collected_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`collected_data`)),
 `is_processed` tinyint(1) DEFAULT 0,
 `is_conflicted` tinyint(1) NOT NULL DEFAULT 0,
 `conflict_reason` text NOT NULL,
 `is_default` tinyint(4) NOT NULL DEFAULT 1,
 `is_reprocessed` tinyint(4) NOT NULL DEFAULT 0,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `created_by` int(11) NOT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `processed_at` datetime DEFAULT NULL,
 `processed_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 `deleted_by` int(11) DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `session_id` (`session_id`),
 KEY `client_id` (`client_id`),
 KEY `domain_id` (`domain_id`),
 KEY `goal_id` (`goal_id`),
 KEY `target_id` (`target_id`),
 KEY `client_probe_set_id` (`client_probe_set_id`),
 KEY `current_phase_id` (`current_phase_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25755 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_session_data_processed`
CREATE TABLE `daily_session_data_processed` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `collection_id` int(11) NOT NULL,
 `session_id` int(11) NOT NULL,
 `session_date` date NOT NULL,
 `client_id` int(11) NOT NULL,
 `domain_id` int(11) NOT NULL,
 `goal_id` int(11) NOT NULL,
 `target_id` int(11) NOT NULL,
 `client_probe_set_id` int(11) NOT NULL,
 `next_phase_id` int(11) NOT NULL,
 `is_program_changed` tinyint(1) NOT NULL,
 `processed_detail` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`processed_detail`)),
 `is_active` tinyint(1) DEFAULT 1,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `created_by` int(11) NOT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 `deleted_by` int(11) DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `collection_id` (`collection_id`),
 KEY `session_id` (`session_id`),
 KEY `client_id` (`client_id`),
 KEY `domain_id` (`domain_id`),
 KEY `goal_id` (`goal_id`),
 KEY `target_id` (`target_id`),
 KEY `client_probe_set_id` (`client_probe_set_id`),
 KEY `next_phase_id` (`next_phase_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32841 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_session_manual`
CREATE TABLE `daily_session_manual` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `week_date` date NOT NULL,
 `client_id` int(11) NOT NULL,
 `instructor_id` int(11) NOT NULL DEFAULT 0,
 `supervisor_id` int(11) NOT NULL DEFAULT 0,
 `hours` double DEFAULT NULL,
 `skills_retained` double DEFAULT NULL,
 `doi` double DEFAULT NULL,
 `total_mands` double DEFAULT NULL,
 `variety_of_mands` double DEFAULT NULL,
 `frequency_of_problem_behavior` double DEFAULT NULL,
 `total_duration_of_problem_behavior` time DEFAULT NULL,
 `session_quality_rating` int(11) DEFAULT NULL,
 `program_change_made` tinyint(4) NOT NULL DEFAULT 0,
 `comments` text DEFAULT NULL,
 `status` int(11) NOT NULL DEFAULT 1,
 `extra_1` date DEFAULT NULL,
 `extra_2` double DEFAULT NULL,
 `extra_3` text DEFAULT NULL,
 `created_by` int(11) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `session_and_client_relation` (`client_id`),
 KEY `session_and_bcba_relation` (`supervisor_id`),
 KEY `session_and_tutor_relation` (`instructor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2849 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_session_manual_weekly`
CREATE TABLE `daily_session_manual_weekly` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `week_date` date NOT NULL,
 `client_id` int(11) NOT NULL,
 `supervisor_id` int(11) NOT NULL DEFAULT 0,
 `hours` double DEFAULT NULL,
 `skills_retained` double DEFAULT NULL,
 `doi` double DEFAULT NULL,
 `status` int(11) NOT NULL DEFAULT 1,
 `created_by` int(11) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `session_and_client_relation` (`client_id`),
 KEY `session_and_bcba_relation` (`supervisor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2813 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_session_processing_log`
CREATE TABLE `daily_session_processing_log` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `session_id` int(11) NOT NULL,
 `processed_at` datetime DEFAULT current_timestamp(),
 `processed_by` int(11) NOT NULL,
 `process_count` int(11) DEFAULT 1,
 `session_status` varchar(20) NOT NULL,
 `total_targets` int(11) DEFAULT 0,
 `processed_success` int(11) DEFAULT 0,
 `conflicted_targets` int(11) DEFAULT 0,
 `deleted_targets` int(11) DEFAULT 0,
 `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
 `session_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_details`)),
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2616 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_session_target_conflict_resolution_log`
CREATE TABLE `daily_session_target_conflict_resolution_log` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `session_id` int(11) NOT NULL,
 `target_id` int(11) NOT NULL,
 `client_id` int(11) NOT NULL,
 `client_probe_set_id` int(11) NOT NULL,
 `conflicted_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`conflicted_data`)),
 `existing_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`existing_data`)),
 `modifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`modifications`)),
 `resolved_by` int(11) NOT NULL,
 `resolved_at` datetime DEFAULT current_timestamp(),
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=473 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `dropdown_items`
CREATE TABLE `dropdown_items` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `category` varchar(50) DEFAULT NULL,
 `value` varchar(255) NOT NULL,
 `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `mands_reinforcer`
CREATE TABLE `mands_reinforcer` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` text NOT NULL,
 `created_by` int(10) unsigned NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(10) unsigned DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6638 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `mands_session_data`
CREATE TABLE `mands_session_data` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `session_date` date NOT NULL,
 `session_id` int(11) NOT NULL,
 `client_id` int(11) NOT NULL,
 `reinforcer_input` text NOT NULL,
 `utterance_input` text DEFAULT NULL,
 `is_peer_manding` tinyint(4) NOT NULL DEFAULT 0,
 `is_eye_contact` tinyint(4) NOT NULL DEFAULT 0,
 `prompt_level` int(11) NOT NULL,
 `mands_error` int(11) DEFAULT NULL,
 `initial_attempt_input` text DEFAULT NULL,
 `initial_attempt` int(11) DEFAULT NULL,
 `prompt_delay_input` text DEFAULT NULL,
 `prompt_delay` int(11) DEFAULT NULL,
 `echoic_1_input` text DEFAULT NULL,
 `echoic_1` int(11) DEFAULT NULL,
 `echoic_2_input` text DEFAULT NULL,
 `echoic_2` int(11) DEFAULT NULL,
 `echoic_3_input` text DEFAULT NULL,
 `echoic_3` int(11) DEFAULT NULL,
 `comparison_prompt_delay` int(11) DEFAULT NULL,
 `comparison_echoic_trial` int(11) DEFAULT NULL,
 `created_by` int(11) NOT NULL,
 `created_at` datetime NOT NULL DEFAULT current_timestamp(),
 `updated_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61527 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `migrations`
CREATE TABLE `migrations` (
 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 `version` varchar(255) NOT NULL,
 `class` varchar(255) NOT NULL,
 `group` varchar(255) NOT NULL,
 `namespace` varchar(255) NOT NULL,
 `time` int(11) NOT NULL,
 `batch` int(11) unsigned NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Table: `program_master_domains`
CREATE TABLE `program_master_domains` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `description` text DEFAULT NULL,
 `domain_code` varchar(50) DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `program_master_goals`
CREATE TABLE `program_master_goals` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `domain_id` int(11) DEFAULT NULL,
 `name` varchar(255) DEFAULT NULL,
 `description` text DEFAULT NULL,
 `goal_code` varchar(50) DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `program_master_targets`
CREATE TABLE `program_master_targets` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `goal_id` int(11) DEFAULT NULL,
 `domain_id` int(11) DEFAULT NULL,
 `name` varchar(255) DEFAULT NULL,
 `description` text DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=464 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `report`
CREATE TABLE `report` (
 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 `report_type` varchar(32) NOT NULL,
 `subject_type` varchar(32) NOT NULL,
 `subject_id` bigint(20) unsigned NOT NULL,
 `period_type` varchar(16) NOT NULL,
 `period_start` date NOT NULL,
 `period_end` date NOT NULL,
 `period_key` varchar(32) NOT NULL,
 `latest_version_no` int(11) NOT NULL DEFAULT 0,
 `created_at` datetime DEFAULT NULL,
 `created_by` bigint(20) unsigned DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` bigint(20) unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `report_type_subject_type_subject_id_period_key` (`report_type`,`subject_type`,`subject_id`,`period_key`),
 KEY `report_type` (`report_type`),
 KEY `subject_type_subject_id` (`subject_type`,`subject_id`),
 KEY `period_start_period_end` (`period_start`,`period_end`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `report_artifact`
CREATE TABLE `report_artifact` (
 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 `report_version_id` bigint(20) unsigned NOT NULL,
 `artifact_type` varchar(16) NOT NULL DEFAULT 'PDF',
 `storage_driver` varchar(16) NOT NULL DEFAULT 'LOCAL',
 `storage_path` varchar(512) NOT NULL,
 `file_name` varchar(255) NOT NULL,
 `mime_type` varchar(128) NOT NULL DEFAULT 'application/pdf',
 `file_size` bigint(20) unsigned DEFAULT NULL,
 `sha256` varchar(64) DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `created_by` bigint(20) unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `report_version_id` (`report_version_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `report_email_log`
CREATE TABLE `report_email_log` (
 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 `report_version_id` bigint(20) unsigned NOT NULL,
 `to_email` varchar(255) NOT NULL,
 `cc_email` varchar(512) DEFAULT NULL,
 `subject` varchar(255) DEFAULT NULL,
 `status` varchar(16) NOT NULL DEFAULT 'PENDING',
 `provider_message_id` varchar(255) DEFAULT NULL,
 `error_message` text DEFAULT NULL,
 `requested_by` bigint(20) unsigned DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `sent_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `report_version_id` (`report_version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `report_template`
CREATE TABLE `report_template` (
 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 `report_type` varchar(32) NOT NULL,
 `template_code` varchar(64) NOT NULL,
 `version_no` int(11) NOT NULL,
 `storage_driver` varchar(16) NOT NULL DEFAULT 'LOCAL',
 `storage_path` varchar(512) NOT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT 1,
 `created_at` datetime DEFAULT NULL,
 `created_by` bigint(20) unsigned DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` bigint(20) unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `report_type_version_no` (`report_type`,`version_no`),
 UNIQUE KEY `report_type_template_code` (`report_type`,`template_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `report_version`
CREATE TABLE `report_version` (
 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 `report_id` bigint(20) unsigned NOT NULL,
 `version_no` int(11) NOT NULL,
 `template_id` bigint(20) unsigned DEFAULT NULL,
 `generation_source` varchar(16) NOT NULL DEFAULT 'MANUAL',
 `data_signature_hash` varchar(64) DEFAULT NULL,
 `generated_at` datetime DEFAULT NULL,
 `generated_by` bigint(20) unsigned DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `created_by` bigint(20) unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `report_id_version_no` (`report_id`,`version_no`),
 KEY `report_id` (`report_id`),
 KEY `template_id` (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `daily_report_version_data`
CREATE TABLE `daily_report_version_data` (
 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 `report_version_id` bigint(20) unsigned NOT NULL,
 `workflow_status` varchar(16) NOT NULL DEFAULT 'DRAFT',
 `is_locked` tinyint(1) NOT NULL DEFAULT 0,
 `manual_json` longtext DEFAULT NULL,
 `snapshot_json` longtext DEFAULT NULL,
 `section_status_json` longtext DEFAULT NULL,
 `finalized_at` datetime DEFAULT NULL,
 `finalized_by` bigint(20) unsigned DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `created_by` bigint(20) unsigned DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` bigint(20) unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `uq_daily_report_version_data_report_version` (`report_version_id`),
 KEY `ix_daily_report_workflow_status` (`workflow_status`),
 CONSTRAINT `fk_daily_report_version_data_report_version` FOREIGN KEY (`report_version_id`) REFERENCES `report_version` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `progress_report_version_data`
CREATE TABLE `progress_report_version_data` (
 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 `report_version_id` bigint(20) unsigned NOT NULL,
 `workflow_status` varchar(16) NOT NULL DEFAULT 'DRAFT',
 `is_locked` tinyint(1) NOT NULL DEFAULT 0,
 `manual_json` longtext DEFAULT NULL,
 `snapshot_json` longtext DEFAULT NULL,
 `section_status_json` longtext DEFAULT NULL,
 `finalized_at` datetime DEFAULT NULL,
 `finalized_by` bigint(20) unsigned DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `created_by` bigint(20) unsigned DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` bigint(20) unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `uq_progress_report_version_data_report_version` (`report_version_id`),
 KEY `ix_progress_report_workflow_status` (`workflow_status`),
 CONSTRAINT `fk_progress_report_version_data_report_version` FOREIGN KEY (`report_version_id`) REFERENCES `report_version` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `settings`
CREATE TABLE `settings` (
 `id` int(9) NOT NULL AUTO_INCREMENT,
 `class` varchar(255) NOT NULL,
 `key` varchar(255) NOT NULL,
 `value` text DEFAULT NULL,
 `type` varchar(31) NOT NULL DEFAULT 'string',
 `context` varchar(255) DEFAULT NULL,
 `created_at` datetime NOT NULL,
 `updated_at` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Table: `target_phases`
CREATE TABLE `target_phases` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `description` varchar(255) NOT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `target_phase_combinations`
CREATE TABLE `target_phase_combinations` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `description` varchar(255) DEFAULT NULL,
 `initial_phase_id` int(11) NOT NULL,
 `final_phase_id` int(11) NOT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `initial_phase_id` (`initial_phase_id`),
 KEY `final_phase_id` (`final_phase_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `target_probe_sets`
CREATE TABLE `target_probe_sets` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `description` varchar(255) DEFAULT NULL,
 `inputs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`inputs`)),
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `target_probe_set_rules`
CREATE TABLE `target_probe_set_rules` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `probe_set_id` int(11) NOT NULL,
 `combination_id` int(11) NOT NULL,
 `phase_id` int(11) NOT NULL,
 `phase_order` int(11) NOT NULL,
 `rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rules`)),
 `created_at` datetime DEFAULT current_timestamp(),
 `created_by` int(11) DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `probe_set_id` (`probe_set_id`),
 KEY `combination_id` (`combination_id`),
 KEY `phase_id` (`phase_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: `users`
CREATE TABLE `users` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `username` varchar(30) DEFAULT NULL,
 `first_name` varchar(255) DEFAULT NULL,
 `last_name` varchar(255) DEFAULT NULL,
 `avatar` varchar(255) DEFAULT NULL,
 `status` varchar(255) DEFAULT NULL,
 `status_message` varchar(255) DEFAULT NULL,
 `active` tinyint(1) NOT NULL DEFAULT 0,
 `last_active` datetime DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 `deleted_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1042 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

SET FOREIGN_KEY_CHECKS=1;
