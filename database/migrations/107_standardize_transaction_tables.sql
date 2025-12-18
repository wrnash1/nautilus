-- Rename pos_transactions to transactions
DROP TABLE IF EXISTS transactions;
RENAME TABLE pos_transactions TO transactions;
