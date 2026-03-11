-- Billing Management System (Core Schema)
-- Compatible with MySQL 5.7+/8.0+ (InnoDB, utf8mb4)

CREATE TABLE `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) DEFAULT NULL,
  `contact_person` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clients_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `proforma_invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proforma_number` varchar(50) NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `proforma_date` date NOT NULL,
  `billing_from` date DEFAULT NULL,
  `billing_to` date DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) NOT NULL DEFAULT 'Draft',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proforma_number` (`proforma_number`),
  KEY `proforma_client_date` (`client_id`,`proforma_date`),
  CONSTRAINT `fk_proforma_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `proforma_id` int(10) unsigned DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `balance_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) NOT NULL DEFAULT 'Unpaid',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `invoice_client_date` (`client_id`,`invoice_date`),
  KEY `invoice_proforma` (`proforma_id`),
  CONSTRAINT `fk_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_invoices_proforma` FOREIGN KEY (`proforma_id`) REFERENCES `proforma_invoices` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `billable_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entry_no` varchar(20) DEFAULT NULL,
  `entry_date` date NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `description` text NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `billing_month` char(7) DEFAULT NULL,
  `proforma_id` int(10) unsigned DEFAULT NULL,
  `invoice_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billable_items_entry_no_unique` (`entry_no`),
  KEY `billable_client_date` (`client_id`,`entry_date`),
  KEY `billable_status_client` (`status`,`client_id`),
  KEY `billable_proforma` (`proforma_id`),
  KEY `billable_invoice` (`invoice_id`),
  CONSTRAINT `fk_billable_items_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_billable_items_proforma` FOREIGN KEY (`proforma_id`) REFERENCES `proforma_invoices` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_billable_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `proforma_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proforma_id` int(10) unsigned NOT NULL,
  `billable_item_id` int(10) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `pi_proforma` (`proforma_id`),
  KEY `pi_billable` (`billable_item_id`),
  CONSTRAINT `fk_proforma_items_proforma` FOREIGN KEY (`proforma_id`) REFERENCES `proforma_invoices` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_proforma_items_billable` FOREIGN KEY (`billable_item_id`) REFERENCES `billable_items` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_date` date NOT NULL,
  `invoice_id` int(10) unsigned NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `reference_number` varchar(100) DEFAULT NULL,
  `remarks` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_client_date` (`client_id`,`payment_date`),
  KEY `payments_invoice` (`invoice_id`),
  CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_payments_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`),
  UNIQUE KEY `admins_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
