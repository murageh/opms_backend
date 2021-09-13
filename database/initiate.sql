
CREATE TABLE IF NOT EXISTS activities (
  id int(11) NOT NULL AUTO_INCREMENT,
  type varchar(50) NOT NULL,
  date_time timestamp NOT NULL DEFAULT current_timestamp(),
  courtesy_of varchar(100) NOT NULL,
  additional_details text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employees (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(100) DEFAULT NULL,
  mobileno varchar(15) DEFAULT NULL,
  salary int(15) NOT NULL DEFAULT 0,
  date_joined timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory (
  id int(11) NOT NULL AUTO_INCREMENT,
  item_name varchar(100) NOT NULL,
  item_quantity int(20) NOT NULL,
  total_cost int(20) NOT NULL,
  date_time timestamp NOT NULL DEFAULT current_timestamp(),
  courtesy_of varchar(100) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sales (
  id int(11) NOT NULL AUTO_INCREMENT,
  type varchar(20) NOT NULL DEFAULT 'undefined.',
  quantity int(20) NOT NULL,
  unit_price int(20) NOT NULL,
  total_price int(20) NOT NULL,
  additional_details text NOT NULL,
  date_time timestamp NOT NULL DEFAULT current_timestamp(),
  attachment text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
