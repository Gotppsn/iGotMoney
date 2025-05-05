-- Database schema for iGotMoney application

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Income sources table
CREATE TABLE IF NOT EXISTS income_sources (
    income_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    frequency ENUM('one-time', 'daily', 'weekly', 'bi-weekly', 'monthly', 'quarterly', 'annually') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Expense categories table
CREATE TABLE IF NOT EXISTS expense_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    is_system BOOLEAN DEFAULT FALSE
);

-- Insert default expense categories
INSERT INTO expense_categories (name, description, is_system) VALUES
('Housing', 'Rent, mortgage, property taxes, repairs', TRUE),
('Utilities', 'Electricity, water, gas, internet, phone', TRUE),
('Food', 'Groceries, restaurants, takeout', TRUE),
('Transportation', 'Car payments, gas, public transit, rideshares', TRUE),
('Insurance', 'Health, auto, home, life insurance', TRUE),
('Healthcare', 'Doctor visits, medications, procedures', TRUE),
('Debt Payments', 'Credit card, student loans, personal loans', TRUE),
('Entertainment', 'Movies, games, hobbies, subscriptions', TRUE),
('Shopping', 'Clothing, electronics, household items', TRUE),
('Personal Care', 'Haircuts, gym, beauty products', TRUE),
('Education', 'Tuition, books, courses, learning materials', TRUE),
('Investments', 'Stocks, bonds, retirement funds', TRUE),
('Gifts & Donations', 'Presents, charitable donations', TRUE),
('Travel', 'Flights, hotels, vacation expenses', TRUE),
('Miscellaneous', 'Other expenses that don\'t fit elsewhere', TRUE);

-- Expenses table
CREATE TABLE IF NOT EXISTS expenses (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    description TEXT,
    expense_date DATE NOT NULL,
    frequency ENUM('one-time', 'daily', 'weekly', 'bi-weekly', 'monthly', 'quarterly', 'annually') NOT NULL DEFAULT 'one-time',
    is_recurring BOOLEAN DEFAULT FALSE,
    next_due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES expense_categories(category_id) ON DELETE RESTRICT
);

-- Budget table
CREATE TABLE IF NOT EXISTS budgets (
    budget_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES expense_categories(category_id) ON DELETE RESTRICT
);

-- Financial goals table
CREATE TABLE IF NOT EXISTS financial_goals (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    target_amount DECIMAL(12, 2) NOT NULL,
    current_amount DECIMAL(12, 2) DEFAULT 0,
    start_date DATE NOT NULL,
    target_date DATE NOT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('in-progress', 'completed', 'cancelled') DEFAULT 'in-progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Investment types table
CREATE TABLE IF NOT EXISTS investment_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    risk_level ENUM('very low', 'low', 'moderate', 'high', 'very high') NOT NULL,
    description TEXT
);

-- Insert default investment types
INSERT INTO investment_types (name, risk_level, description) VALUES
('Savings Account', 'very low', 'Bank savings accounts with minimal interest'),
('Certificate of Deposit', 'low', 'Fixed-term deposits with banks'),
('Government Bonds', 'low', 'Debt securities issued by governments'),
('Corporate Bonds', 'moderate', 'Debt securities issued by corporations'),
('Mutual Funds', 'moderate', 'Pooled funds managed by professionals'),
('ETFs', 'moderate', 'Exchange-traded funds that track indexes'),
('Stocks', 'high', 'Shares of individual companies'),
('Real Estate', 'high', 'Property investments'),
('Commodities', 'high', 'Raw materials like gold, oil, etc.'),
('Cryptocurrency', 'very high', 'Digital currencies like Bitcoin');

-- Investments table
CREATE TABLE IF NOT EXISTS investments (
    investment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    ticker_symbol VARCHAR(10),
    purchase_date DATE NOT NULL,
    purchase_price DECIMAL(12, 2) NOT NULL,
    quantity DECIMAL(12, 6) NOT NULL,
    current_price DECIMAL(12, 2),
    last_updated TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES investment_types(type_id) ON DELETE RESTRICT
);

-- Stock watchlist table
CREATE TABLE IF NOT EXISTS stock_watchlist (
    watchlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticker_symbol VARCHAR(10) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    target_buy_price DECIMAL(12, 2),
    target_sell_price DECIMAL(12, 2),
    current_price DECIMAL(12, 2),
    last_updated TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Tax information table
CREATE TABLE IF NOT EXISTS tax_information (
    tax_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tax_year YEAR NOT NULL,
    filing_status ENUM('single', 'married_joint', 'married_separate', 'head_of_household') NOT NULL,
    estimated_income DECIMAL(12, 2) NOT NULL,
    tax_paid_to_date DECIMAL(12, 2) DEFAULT 0,
    deductions DECIMAL(12, 2) DEFAULT 0,
    credits DECIMAL(12, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Transactions table (for all financial transactions)
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('income', 'expense', 'transfer', 'investment') NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    description TEXT,
    transaction_date DATE NOT NULL,
    category_id INT,
    income_id INT,
    expense_id INT,
    investment_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES expense_categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (income_id) REFERENCES income_sources(income_id) ON DELETE SET NULL,
    FOREIGN KEY (expense_id) REFERENCES expenses(expense_id) ON DELETE SET NULL,
    FOREIGN KEY (investment_id) REFERENCES investments(investment_id) ON DELETE SET NULL
);

-- Financial advice table
CREATE TABLE IF NOT EXISTS financial_advice (
    advice_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('budgeting', 'saving', 'investment', 'tax', 'debt', 'general') NOT NULL,
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    importance_level ENUM('low', 'medium', 'high') DEFAULT 'medium',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- User settings
CREATE TABLE IF NOT EXISTS user_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    currency VARCHAR(3) DEFAULT 'USD',
    theme ENUM('light', 'dark', 'system') DEFAULT 'system',
    notification_enabled BOOLEAN DEFAULT TRUE,
    email_notification_enabled BOOLEAN DEFAULT TRUE,
    budget_alert_threshold INT DEFAULT 80,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);