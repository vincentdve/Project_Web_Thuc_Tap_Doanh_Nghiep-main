# Ghi chú thay đổi code – phần giảng viên / admin



## 1. File PHP thêm mới

| File | Nội dung |
|---|---|
| `admin/assign-lecturer.php` | Trang admin phân công / thay đổi giảng viên phụ trách kỳ thực tập. |
| `lecturer/evaluate-internship.php` | Trang giảng viên nhập / sửa điểm và nhận xét **tổng kết kỳ thực tập**. |

## 2. Một bảng CSDL mới: `report_evaluations`

Bảng này lưu **điểm và nhận xét của từng báo cáo**, tách biệt với bảng `evaluations` đang dùng cho **đánh giá tổng kết kỳ thực tập**.


```sql
CREATE TABLE IF NOT EXISTS report_evaluations (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, report_id INT UNSIGNED NOT NULL, evaluator_user_id INT UNSIGNED NOT NULL, score DECIMAL(4,2) DEFAULT NULL, comments TEXT DEFAULT NULL, evaluated_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_report_evaluator (report_id, evaluator_user_id));
```


## 3. Các file đã chỉnh sửa

| File | Thay đổi chính |
|---|---|
| `lecturer/reports.php` | Lưu / đọc điểm từng báo cáo từ `report_evaluations`; sửa lỗi đóng statement lặp. |
| `student-portal.php` | Hiển thị điểm và nhận xét từng báo cáo cho sinh viên. |
| `lecturer/internships.php` | Hiển thị điểm tổng kết và liên kết sang trang đánh giá kỳ thực tập. |
| `lecturer/logs.php` | Bổ sung / điều chỉnh phần giảng viên xem nhật ký; **đây là file có sẵn, không phải file mới**. |
| `admin/_common.php` | Thêm đường dẫn menu đến trang phân công giảng viên. |
| `sql/phplogin.sql` | Bổ sung định nghĩa bảng `report_evaluations`. |

## 4. Chức năng đã bổ sung

- **Admin – Phân công giảng viên:** Xem danh sách kỳ thực tập và phân công hoặc thay đổi giảng viên phụ trách.
- **Giảng viên – Xem nhật ký:** Xem nhật ký thực tập của sinh viên thuộc kỳ thực tập được phân công (bổ sung trên trang có sẵn).
- **Giảng viên – Chấm từng báo cáo:** Nhập, lưu và xem lại điểm, nhận xét cho từng báo cáo; điểm được lưu riêng trong `report_evaluations`.
- **Sinh viên – Xem kết quả báo cáo:** Xem điểm và nhận xét của giảng viên đối với từng báo cáo.
- **Giảng viên – Đánh giá tổng kết:** Nhập hoặc sửa điểm và nhận xét tổng kết kỳ thực tập, tách biệt với điểm từng báo cáo.

