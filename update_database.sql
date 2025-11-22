ALTER TABLE eventcore_vendors 
CHANGE COLUMN registered_name business_name VARCHAR(255),
CHANGE COLUMN brn_nic registration_number VARCHAR(100),
CHANGE COLUMN website_links social_links TEXT,
CHANGE COLUMN account_username username VARCHAR(100),
CHANGE COLUMN declarant_name vendor_name VARCHAR(255),
CHANGE COLUMN date_signed date DATE;