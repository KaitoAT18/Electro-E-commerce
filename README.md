# ELECTRO SHOPPING WEB APP - HƯỚNG DẪN CÀI ĐẶT

## Mục lục

-   [Giới thiệu](#giới-thiệu)
-   [Yêu cầu hệ thống](#yêu-cầu-hệ-thống)
-   [Cài đặt bằng XAMPP](#cài-đặt)
-   [Cài đặt bằng Docker](#cài-đặt-bằng-docker)
-   [Tài khoản demo](#tài-khoản-demo)

## Giới thiệu

Electro là một hệ thống thương mại điện tử được xây dựng bằng PHP thuần, cung cấp giải pháp mua bán sản phẩm điện tử trực tuyến. Với giao diện thân thiện và tính năng đầy đủ, Electro giúp quản lý hiệu quả cửa hàng điện tử từ sản phẩm, danh mục, đơn hàng đến người dùng và thanh toán.

## Yêu cầu hệ thống

-   **PHP**:
    -   Phiên bản: PHP 8.0+
    -   Extensions: PDP
-   **Cơ sở dữ liệu**:
    -   MySQL 5.7+ hoặc MariaDB 10.3+
-   **Công cụ:**
    -   Composer (khuyến nghị cho quản lý các dependencies).

## Cài đặt bằng XAMPP

### 1. Chuẩn bị môi trường

-   Cài đặt XAMPP từ [trang chủ](https://www.apachefriends.org/download.html)
-   Khởi động dịch vụ **Apache Web Server** và **MySQL Database**.

### 2. Tải dự án

-   Vào thư mục cài đặt XAMPP, rồi vào thư mục `htdocs`.
-   Clone dự án từ Github: `git clone https://github.com/KaitoAT18/Electro-E-commerce.git`.

### 3. Cấu hình cơ sở dữ liệu

-  Truy cập vào [phpMyAdmin](http://localhost/phpmyadmin).
- Tạo cơ sở dữ liệu mới có tên `shopping_web` với collaction `utf8mb4_unicode_ci`.
- Import file SQL:
    - Mở tab "Import" trong **phpMyAdmin**.
    - Chọn file `sql/shopping_web.sql` từ thư mục của dự án.
    - Nhấn "Go".
- Cấu hình môi trường
    - Tạo file `.env` từ file mẫu `.env.example`.
    - Chỉnh sửa file `.env` để điều chỉnh các thông số cấu hình phù hợp.

## Cài đặt bằng Docker

### 1. Chuẩn bị môi trường

-   Cài đặt Docker từ [trang chủ](https://www.docker.com/get-started)
-   Khởi động dịch vụ Docker.

### 2. Tải dự án

-   Clone dự án từ Github: `git clone https://github.com/KaitoAT18/Electro-E-commerce.git`.

### 3. Khởi động dịch vụ

-   Vào thư mục dự án và chạy lệnh sau để khởi động dịch vụ:
    ```bash
    docker-compose up -d
    ```
### 4. Truy cập trang web
- Sau khi khởi động dịch vụ thành công, có thể truy cập vào website ở địa chỉ `http://localhost:8080/public/`.

## Tài khoản demo

### Admin:

-   Username: `minhlh56`
-   Password: `Minh562003@`

### User:

-   Username: `yinghao36`
-   Password: `Yinghao36@`
