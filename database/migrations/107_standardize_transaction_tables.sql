-- Rename pos_transactions to transactions
-- Simplified for PDO compatibility

DROP TABLE IF EXISTS transactions;
RENAME TABLE pos_transactions TO transactions;

-- Migration complete
