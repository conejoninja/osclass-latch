CREATE TABLE  /*TABLE_PREFIX*/t_latch (
    fk_i_user_id INT UNSIGNED NOT NULL,
    s_account_id VARCHAR(64) NULL ,

        PRIMARY KEY (fk_i_user_id),
        FOREIGN KEY (fk_i_user_id) REFERENCES /*TABLE_PREFIX*/t_user (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';
