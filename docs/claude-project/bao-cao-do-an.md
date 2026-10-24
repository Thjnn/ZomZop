
**KHOA KỸ THUẬT CÔNG NGHỆ**

-----🙞🙜🕮🙞🙜-----


**BÁO CÁO ĐỒ ÁN CHUYÊN NGÀNH**

> **XÂY DỰNG WEB APP CỬA HÀNG**
>
> **BÁN ĐỒ ĂN NHANH – ZOMZOP**

**Giảng viên hướng dẫn:** **Sinh viên thực hiện:**

ThS. Huỳnh Thị Nhật Hằng 023101006 – Ngô Hữu Tính

023101057 – Cao Phúc Thịnh

023101058 – Nguyễn Văn Trực

**Lớp:** ĐH CNTT 23A

**Đồng Tháp, ngày 19 tháng 10 năm 2026**

# LỜI NÓI ĐẦU

Cùng với sự phát triển của Internet và thiết bị di động, thói quen ăn uống và mua sắm của người dân đã thay đổi rõ rệt. Việc đặt món qua mạng ngày càng phổ biến, nhất là ở nhóm học sinh, sinh viên và người đi làm, những người có ít thời gian và ưu tiên sự nhanh gọn. Thức ăn nhanh là loại sản phẩm phù hợp với hình thức này, bởi món ăn được chế biến trong thời gian ngắn, giá thành vừa phải và thuận tiện cho việc giao nhận.

Hiện nay, nhiều cửa hàng thức ăn nhanh bán hàng chủ yếu thông qua các nền tảng giao đồ ăn trung gian. Hình thức này giúp cửa hàng tiếp cận khách hàng nhanh chóng, nhưng đổi lại phải chịu chi phí hoa hồng và gần như không chủ động được dữ liệu khách hàng, chương trình khuyến mãi hay cách thể hiện thương hiệu. Do đó, việc xây dựng một ứng dụng web riêng cho cửa hàng là hướng đi hợp lý, vừa giữ được quyền chủ động trong kinh doanh, vừa tạo ra kênh bán hàng ổn định cho khách quen.

Xuất phát từ thực tế trên, nhóm lựa chọn thực hiện đề tài “Xây dựng web app cửa hàng bán đồ ăn nhanh – ZomZop”. Hệ thống được thiết kế cho mô hình chuỗi cửa hàng có nhiều chi nhánh. Khách hàng có thể chọn chi nhánh, tìm món, xem thực đơn theo danh mục, thêm món vào giỏ hàng, đặt hàng mang đi hoặc giao tận nơi, sử dụng mã giảm giá, theo dõi và đánh giá đơn hàng, đồng thời được trợ lý chatbot hỗ trợ chọn món. Về phía vận hành, quản lý chi nhánh xử lý đơn hàng, điều chỉnh thực đơn và giá bán, quản lý nhân sự, ca làm, chấm công bằng nhận diện khuôn mặt, bảng lương và báo cáo; quản trị viên điều hành dữ liệu chung của toàn chuỗi.

Về công nghệ, đề tài được xây dựng trên framework Laravel 13, sử dụng cơ sở dữ liệu MySQL; giao diện được thiết kế bằng Blade template kết hợp Tailwind CSS và JavaScript, hiển thị phù hợp trên cả máy tính và điện thoại. Hệ thống tích hợp thư viện face-api.js để nhận diện khuôn mặt ngay trên trình duyệt và dịch vụ Google Gemini để trả lời câu hỏi của khách hàng.

Nội dung báo cáo gồm ba chương. Chương 1 trình bày lý do chọn đề tài, mục tiêu, phạm vi chức năng, phân công công việc và tiến độ thực hiện. Chương 2 trình bày việc phân tích yêu cầu, thiết kế cơ sở dữ liệu và xây dựng hệ thống, kèm theo các giao diện chính. Chương 3 nêu kết quả đạt được và phương hướng phát triển tiếp theo của đề tài.

# LỜI CẢM ƠN

Để hoàn thành đồ án chuyên ngành này, nhóm chúng em đã nhận được sự hỗ trợ từ nhiều tập thể và cá nhân. Trước hết, nhóm xin gửi lời cảm ơn đến Ban Giám hiệu Trường Đại học Tiền Giang cùng quý Thầy Cô Khoa Kỹ thuật Công nghệ đã tạo điều kiện thuận lợi về môi trường học tập và cơ sở vật chất trong suốt quá trình theo học. Các kiến thức về lập trình web, cơ sở dữ liệu, phân tích và thiết kế hệ thống mà nhóm tiếp thu ở những học phần trước là nền tảng trực tiếp để thực hiện đề tài này.

Nhóm đặc biệt cảm ơn ThS. Huỳnh Thị Nhật Hằng, giảng viên hướng dẫn đồ án. Trong thời gian thực hiện, Cô đã theo dõi tiến độ của nhóm, góp ý về hướng triển khai và chỉ ra những điểm chưa hợp lý trong khâu phân tích, thiết kế cũng như trong cách trình bày báo cáo. Nhờ những nhận xét đó, nhóm kịp thời chỉnh sửa và hoàn thiện sản phẩm theo đúng yêu cầu đề ra.

Do thời gian thực hiện có hạn và kinh nghiệm thực tế còn hạn chế, đồ án khó tránh khỏi những thiếu sót. Nhóm rất mong nhận được ý kiến đóng góp của quý Thầy Cô để sản phẩm được hoàn thiện hơn. Nhóm xin chân thành cảm ơn.

Đồng Tháp, ngày 19 tháng 10 năm 2026

Nhóm sinh viên thực hiện

**MỤC LỤC**

[LỜI NÓI ĐẦU](#gwqjxc1fqnwp) 1

[LỜI CẢM ƠN](#l0ob9oqu0ap) 2

[DANH MỤC TỪ VIẾT TẮT](#6b0d6vh0far1) 7

[CHƯƠNG 1: GIỚI THIỆU](#xe2bpqe4lt1j) 8

> [1.1. LÝ DO CHỌN ĐỀ TÀI](#z6mds14d6tpr) 8
>
> [1.1.1. Mục tiêu, nhiệm vụ và ý nghĩa đề tài](#bihzgb63r9uh) 8
>
> [1.1.2. Mục tiêu đề tài](#10thcvrybtiw) 9
>
> [1.2. PHÂN CÔNG CÔNG VIỆC VÀ TIẾN ĐỘ THỰC HIỆN](#17ejtdfi78do) 12
>
> [1.2.1. Phân công công việc](#uaeuxggsgtu3) 12
>
> [1.2.2. Tiến trình thực hiện](#6x8vc6q8zd57) 13

[CHƯƠNG 2: PHÂN TÍCH, THIẾT KẾ VÀ XÂY DỰNG PHẦN MỀM](#2u1t7ydzbw8q) 14

> [2.1. PHÂN TÍCH](#w8bffrxm1k4w) 14
>
> [2.1.1. Bối cảnh thực tiễn và tính cấp thiết của đề tài](#5v5mhypbdky1) 14
>
> [2.1.2. Phương pháp và cách thức giải quyết bài toán](#tvjx285clxed) 14
>
> [2.1.3. Quy trình hoạt động của hệ thống](#egltwnj6j9mr) 15
>
> [2.1.4. Cơ sở lý thuyết và công nghệ sử dụng](#vfyph2foxn1c) 16
>
> [2.1.5. Phân tích bài toán](#ms2inf3lf3q4) 17
>
> [2.1.6. Yêu cầu phần mềm](#36ykija0xyu2) 19
>
> [2.1.7. Sơ đồ Use Case](#2ytea7hmz4z) 27
>
> [2.2. THIẾT KẾ VÀ XÂY DỰNG](#213hw3pwv1u7) 52
>
> [2.2.1. Thiết kế kiểu dữ liệu](#ak4u22x0atpi) 52
>
> [2.3. GIAO DIỆN HỆ THỐNG](#hm37ugahtu31) 63
>
> [2.3.1. Giao diện phía khách hàng](#ausnghtrs6dt) 63
>
> [2.3.2. Giao diện quản lý chi nhánh](#c38epvu9vdsg) 73
>
> [2.3.3. Giao diện quản trị viên](#9pah9imezjv1) 75
>
> [2.3.4. Giao diện nhân viên](#t4ylvmjdsjgd) 76
>
> [2.3.5. Giao diện đầu bếp](#aoeyhsrb9efc) 76

[CHƯƠNG 3: KẾT QUẢ VÀ PHƯƠNG HƯỚNG PHÁT TRIỂN](#7xzrmaffnnf0) 78

> [3.1. KẾT QUẢ ĐẠT ĐƯỢC](#cfw1nrq4p46k) 78
>
> [3.1.1. Kết quả thực hiện các chức năng](#ffr670pinn5i) 78
>
> 3.1.2. Kết quả kiểm thử
>
> [3.1.3. Kết luận chung](#7zm70cjxawq) 79
>
> [3.2. PHƯƠNG HƯỚNG PHÁT TRIỂN](#r2vn6buva5zy) 80

[TÀI LIỆU THAM KHẢO](#8ny0njthmic2) 82

**DANH MỤC HÌNH ẢNH**

[Hình 1: Sơ đồ use case tổng quát.](#wwfmf6kl74nw) 29

[Hình 2: Sơ đồ use case đăng ký.](#2soi1hk3ugoy) 30

[Hình 3: Sơ đồ use case đăng nhập và đăng xuất.](#lpuyrcxieryx) 30

[Hình 4: Sơ đồ use case tra cứu thực đơn và đặt hàng.](#fx3pgn95snx5) 30

[Hình 5: Sơ đồ use case thanh toán đơn hàng.](#ek2d3ij1dvf7) 31

[Hình 6: Sơ đồ use case quản lý hồ sơ và theo dõi đơn hàng.](#izc3g8sli89u) 31

[Hình 7: Sơ đồ use case đánh giá đơn hàng.](#fzkp3x9ogiwf) 32

[Hình 8: Sơ đồ use case tư vấn thực đơn bằng trợ lý AI.](#7mb48hh3nc2k) 32

[Hình 9: Sơ đồ use case xử lý đơn hàng chi nhánh.](#5067gx96j2j0) 33

[Hình 10: Sơ đồ use case quản lý thực đơn và giá bán chi nhánh.](#3y2oe3sgq95y) 33

[Hình 11: Sơ đồ use case quản lý nhân sự và ca làm việc.](#rmim2z1nduci) 34

[Hình 12: Sơ đồ use case chấm công bằng nhận diện khuôn mặt.](#iwze2hfhnprx) 34

[Hình 13: Sơ đồ use case quản lý bảng lương.](#ab433nxzjxgz) 35

[Hình 14: Sơ đồ use case báo cáo doanh thu.](#6uavlsg7v01b) 35

[Hình 15: Sơ đồ use case theo dõi báo cáo và đánh giá chi nhánh.](#e0wxaxaobgwl) 36

[Hình 16: Sơ đồ use case quản trị hệ thống và cấp tài khoản quản lý.](#8gi2wag3cv0f) 36

[Hình 17: Sơ đồ class.](#v6z694bcisg4) 52

[Hình 18: Sơ đồ cơ sở dữ liệu quan hệ.](#at55lal0n0dr) 52

[Hình 19: Giao diện trang chủ.](#19qu4k6w0v6i) 63

[Hình 20: Giao diện danh mục.](#5ornwdzggec) 63

[Hình 21: Giao diện đăng ký.](#frt011cgdvcx) 64

[Hình 22: Giao diện đăng nhập.](#y069qejvcmal) 64

[Hình 23: Giao diện menu khách hàng.](#pm4oujkoe2q5) 64

[Hình 24: Giao diện hồ sơ khách hàng.](#szs8v8un78t5) 65

[Hình 25: Giao diện đơn hàng.](#7b9yi6dgd5jy) 65

[Hình 26: Giao diện trang yêu thích.](#ia7o1cv15le0) 65

[Hình 27: Giao diện trang thông báo khách hàng.](#lfee7cqymy3g) 66

[Hình 28: Giao diện voucher.](#mna6stpfgcdt) 66

[Hình 29: Giao diện địa chỉ.](#esuds5uh5n21) 67

[Hình 30: Giao diện về chúng tôi.](#fx7klqwqpx64) 67

[Hình 31: Giao diện hỗ trợ.](#etf3dprz67ok) 67

[Hình 32: Giao diện chính sách bảo mật.](#e99bxs9oquvp) 68

[Hình 33: Giao diện chọn chi nhánh.](#812218fo5z80) 68

[Hình 34: Giao diện giỏ hàng.](#h6gu6fq03gyd) 69

[Hình 35: Giao diện thanh toán.](#iq0g5x6a5bkt) 69

[Hình 36: Giao diện thanh toán thành công.](#54q0ddps6eki) 69

[Hình 37: Giao diện tìm kiếm món ngay khi gõ.](#jmus5dvmst6t) 70

[Hình 38: Giao diện món bán chạy của chi nhánh.](#pz8tl1k7bt83) 70

[Hình 39: Giao diện trợ lý chatbot.](#e28154ac2ndt) 71

[Hình 40: Giao diện trên điện thoại.](#vxzz7caaxja2) 72

[Hình 41: Giao diện tổng quan chi nhánh.](#27w0dgfvzfrb) 73

[Hình 42: Giao diện thực đơn và giá bán chi nhánh.](#pe6r0d6m2sny) 74

[Hình 43: Giao diện báo cáo doanh thu.](#1nqbqcv2nkbu) 74

[Hình 44: Giao diện tổng quan toàn chuỗi.](#8gxhbsnjrfo9) 75

[Hình 45: Giao diện quản lý từ khoá chatbot.](#d4qo418q1l5o) 75

[Hình 46: Giao diện tổng quan nhân viên.](#2bclclb5cxit) 76

[Hình 47: Giao diện tổng quan đầu bếp.](#8vzrx79pu7mf) 76

[Hình 48: Giao diện màn hình bếp.](#4n8jixgdnuxn) 77

**DANH MỤC BẢNG**

[Bảng 1: Phân công công việc.](#vrzpjhqka6yw) 12

[Bảng 2: Tiến trình thực hiện.](#7kg5h0jdp1f0) 13

[Bảng 3: Các chức năng chính của hệ thống.](#dgchxavcu22d) 19

[Bảng 4: Mối liên hệ giữa người dùng và chức năng.](#g0o5gl6hh9ms) 22

[Bảng 5: Danh sách các use case.](#1m1d1zvift07) 27

[Bảng 6: Danh sách các actor.](#u6n6fu5pm9hd) 29

[Bảng 7: Đặc tả UC01 – Đăng ký.](#r46ie4srx5yf) 37

[Bảng 8: Đặc tả UC02 – Đăng nhập và đăng xuất.](#q7tts5u9hryx) 38

[Bảng 9: Đặc tả UC03 – Tra cứu thực đơn và đặt hàng.](#3fbszvlgqvji) 39

[Bảng 10: Đặc tả UC04 – Thanh toán đơn hàng.](#unk27ykaeklw) 40

[Bảng 11: Đặc tả UC05 – Quản lý hồ sơ và theo dõi đơn hàng.](#mbi78yaehvzf) 41

[Bảng 12: Đặc tả UC06 – Đánh giá đơn hàng.](#h3wh5wcf5t4z) 42

[Bảng 13: Đặc tả UC07 – Tư vấn thực đơn bằng trợ lý AI.](#mezaebaqcn5b) 43

[Bảng 14: Đặc tả UC08 – Xử lý đơn hàng chi nhánh.](#jucos9i0idnc) 44

[Bảng 15: Đặc tả UC09 – Quản lý thực đơn và giá bán chi nhánh.](#wggjfi3ty30w) 45

[Bảng 16: Đặc tả UC10 – Quản lý nhân sự và ca làm việc.](#4untiw53bn4n) 46

[Bảng 17: Đặc tả UC11 – Chấm công bằng nhận diện khuôn mặt.](#qc47wvqbbvo9) 47

[Bảng 18: Đặc tả UC12 – Quản lý bảng lương.](#tgb2glyy8nks) 48

[Bảng 19: Đặc tả UC13 – Báo cáo doanh thu.](#ze7pb31l2bs4) 49

[Bảng 20: Đặc tả UC14 – Theo dõi báo cáo và đánh giá chi nhánh.](#p8iqtoq0nsq4) 50

[Bảng 21: Đặc tả UC15 – Quản trị tài khoản hệ thống và cấp phát tài khoản quản lý.](#cnzvpiy4xyuc) 51

[Bảng 22: Đặc tả dữ liệu bảng branches.](#dhkb43mrjsmf) 53

[Bảng 23: Đặc tả dữ liệu bảng users.](#uumgz32tfg2d) 54

[Bảng 24: Đặc tả dữ liệu bảng categories.](#s5w0hoe6yp3) 55

[Bảng 25: Đặc tả dữ liệu bảng menu_items.](#yuhcqsupc47a) 55

[Bảng 26: Đặc tả dữ liệu bảng branch_menu_items.](#stapx54zq4ai) 56

[Bảng 27: Đặc tả dữ liệu bảng orders.](#vwtsscph378r) 57

[Bảng 28: Đặc tả dữ liệu bảng order_items.](#7lkh5eyvcmxe) 58

[Bảng 29: Đặc tả dữ liệu bảng coupons.](#xiae8y5xbyl2) 59

[Bảng 30: Đặc tả dữ liệu bảng reviews.](#8mdgowv87ahz) 60

[Bảng 31: Đặc tả dữ liệu bảng ai_chat_sessions.](#ekrge9ooyrjd) 60

[Bảng 32: Đặc tả dữ liệu bảng shifts.](#l5ke99qptujc) 61

[Bảng 33: Đặc tả dữ liệu bảng attendances.](#dzulxr39t7j1) 61

[Bảng 34: Đặc tả dữ liệu bảng payrolls.](#mc8j8gawkv) 62

[Bảng 35: Đặc tả dữ liệu bảng face_descriptors.](#8l8mdjo8jzop) 63

Bảng 36: Đặc tả dữ liệu bảng order_histories.

Bảng 37: Đặc tả dữ liệu bảng payroll_adjustments.

Bảng 38: Đặc tả dữ liệu bảng shift_assignments.

Bảng 39: Đặc tả dữ liệu bảng kiosk_devices.

Bảng 40: Một số kịch bản kiểm thử tiêu biểu.

**DANH MỤC TỪ VIẾT TẮT**

| **STT** | **Từ viết tắt** | **Tên viết tắt**                  |
|:-------:|-----------------|-----------------------------------|
|  **1**  | UC              | Use Case                          |
|  **2**  | B2C             | Business to Consumer              |
|  **3**  | API             | Application Programming Interface |
|  **4**  | UML             | Unified Modeling Language         |
|  **5**  | COD             | Cash on Delivery                  |
|  **6**  | PHP             | Hypertext Preprocessor            |
|  **7**  | HTML            | HyperText Markup Language         |
|  **8**  | CSS             | Cascading Style Sheets            |
|  **9**  | UI/UX           | User Interface / User Experience  |
| **10**  | MVC             | Model – View – Controller         |
| **11**  | ORM             | Object Relational Mapping         |
| **12**  | CSDL            | Cơ sở dữ liệu                     |
| **13**  | AI              | Artificial Intelligence           |
| **14**  | CSRF            | Cross-Site Request Forgery        |

# CHƯƠNG 1: GIỚI THIỆU

## 1.1. LÝ DO CHỌN ĐỀ TÀI

Thức ăn nhanh đã trở thành một phần quen thuộc trong sinh hoạt hằng ngày ở các khu đô thị. Cùng với đó, nhu cầu đặt món trực tuyến tăng nhanh trong những năm gần đây. Đối với khách hàng, việc đặt món qua web hoặc điện thoại giúp tiết kiệm thời gian di chuyển, dễ dàng so sánh món ăn, giá bán và các chương trình ưu đãi trước khi quyết định.

Tuy nhiên, qua khảo sát thực tế, nhóm nhận thấy nhiều cửa hàng vẫn phụ thuộc vào các nền tảng trung gian. Cách làm này làm tăng chi phí vận hành, khiến thương hiệu của cửa hàng bị mờ nhạt giữa nhiều cửa hàng khác và dữ liệu về đơn hàng, khách hàng không nằm trong tay chủ cửa hàng. Đối với các cửa hàng có nhiều chi nhánh, việc thống nhất thực đơn, giá bán và chương trình khuyến mãi giữa các điểm bán cũng gặp nhiều khó khăn nếu quản lý thủ công.

Từ những lý do trên, nhóm chọn thực hiện đề tài “Xây dựng web app cửa hàng bán đồ ăn nhanh – ZomZop”. Đây cũng là cơ hội để nhóm vận dụng các kiến thức đã học về lập trình web, cơ sở dữ liệu, phân tích và thiết kế hệ thống vào một bài toán có quy trình nghiệp vụ tương đối đầy đủ, từ khâu trưng bày thực đơn, đặt hàng cho đến quản lý đơn hàng, nhân sự và khuyến mãi.

### 1.1.1. Mục tiêu, nhiệm vụ và ý nghĩa đề tài

**a. Mục tiêu chính**

Xây dựng web app bán đồ ăn nhanh cho phép khách hàng thực hiện trọn vẹn quy trình từ xem thực đơn, chọn món, đặt hàng đến theo dõi đơn hàng của mình.

Cung cấp cho cửa hàng công cụ quản lý thực đơn, chi nhánh, đơn hàng và khuyến mãi trên cùng một hệ thống.

Thiết kế giao diện rõ ràng, dễ thao tác và hiển thị tốt trên cả máy tính lẫn điện thoại.

**b. Nhiệm vụ cụ thể**

Khảo sát nghiệp vụ bán đồ ăn nhanh trực tuyến và tham khảo một số website đặt món hiện có để xác định các chức năng cần xây dựng.

Phân tích yêu cầu, xây dựng sơ đồ use case và thiết kế cơ sở dữ liệu quan hệ cho hệ thống.

Lập trình phân hệ dành cho khách hàng và phân hệ dành cho quản trị.

Thiết kế giao diện thích ứng với nhiều kích thước màn hình.

Kiểm thử, sửa lỗi và hoàn thiện báo cáo.

**c. Ý nghĩa thực tiễn**

Về mặt thực tiễn, hệ thống giúp khách hàng đặt món nhanh hơn, ít thao tác hơn và nắm được các ưu đãi đang áp dụng. Đối với cửa hàng, hệ thống tập trung dữ liệu đơn hàng và khách hàng vào một nơi, hạn chế việc ghi chép rời rạc và giúp theo dõi hoạt động kinh doanh của từng chi nhánh thuận lợi hơn.

Về mặt học tập, đề tài giúp các thành viên làm quen với quy trình phát triển một phần mềm web hoàn chỉnh: từ phân tích, thiết kế, lập trình, kiểm thử đến viết tài liệu. Qua đó, nhóm có điều kiện thực hành framework Laravel, làm việc với cơ sở dữ liệu MySQL và rèn luyện kỹ năng phối hợp trong nhóm.

### 1.1.2. Mục tiêu đề tài

**a. Phân hệ Khách hàng**

**Trang chủ và thực đơn theo chi nhánh:** Khách hàng chọn chi nhánh muốn mua trước khi xem thực đơn, vì mỗi chi nhánh có thể áp dụng giá bán và tình trạng còn món khác nhau. Thực đơn được sắp xếp theo danh mục, kèm hình ảnh, mô tả và giá bán đang hiệu lực tại chi nhánh đã chọn.

**Tìm kiếm, giỏ hàng và đặt hàng:** Khách hàng tìm món ngay khi gõ trên thanh tìm kiếm, lọc theo danh mục, xem mục Bán chạy của chi nhánh, thêm món vào giỏ kèm ghi chú và topping (nếu món có) và chọn một trong hai hình thức nhận hàng là mang đi hoặc giao hàng tận nơi. Với đơn giao hàng, khách hàng chọn địa chỉ trong sổ địa chỉ đã lưu và chọn tốc độ giao tiêu chuẩn hoặc hỏa tốc.

**Thanh toán và ưu đãi:** Trang thanh toán cung cấp bốn lựa chọn gồm tiền mặt, chuyển khoản qua mã VietQR, ví MoMo và VNPay; lựa chọn được lưu kèm đơn hàng để phục vụ đối soát, riêng đơn tiền mặt tự động được ghi nhận đã thanh toán khi đơn hoàn thành, còn đơn chuyển khoản được quản lý xác nhận đã thanh toán sau khi đối chiếu tiền về tài khoản. Phí giao hàng là 15.000đ cho giao tiêu chuẩn và 30.000đ cho giao hỏa tốc. Khách hàng nhập hoặc chọn mã giảm giá trong danh sách gợi ý; mỗi mã áp dụng cho tiền món, phí giao tiêu chuẩn hoặc phí giao hỏa tốc, và hệ thống giải thích rõ lý do khi một mã chưa dùng được. Tổng tiền bằng tiền món cộng phí giao hàng trừ số tiền được giảm.

**Tài khoản cá nhân:** Sau khi đăng ký và đăng nhập, khách hàng quản lý hồ sơ, sổ địa chỉ giao hàng, món ăn yêu thích, kho mã giảm giá, thông báo và toàn bộ lịch sử đơn hàng. Trạng thái của từng đơn được hiển thị theo sáu mức: chờ xác nhận, đã xác nhận, đang nấu, sẵn sàng, hoàn thành và đã huỷ; khách hàng có thể đặt lại một đơn cũ, khi đó hệ thống áp dụng giá hiện hành và bỏ qua món đã ngừng bán. Khách hàng có thể đăng nhập nhanh bằng tài khoản Google hoặc Facebook, và tự huỷ đơn kèm lý do khi đơn còn chờ xác nhận và chưa thanh toán; lượt dùng mã giảm giá và số lượng tồn được hoàn lại khi đơn bị huỷ.

**Đánh giá sau khi mua:** Mỗi đơn hàng đã hoàn thành được gửi một đánh giá, gồm số sao cho đơn hàng, số sao riêng cho khâu giao hàng và nội dung nhận xét. Đánh giá này được chuyển đến quản lý của chi nhánh đã phục vụ đơn.

**Trợ lý chatbot tư vấn thực đơn:** Khung hội thoại ở góc màn hình cho phép khách hàng đã đăng nhập đặt câu hỏi bằng tiếng Việt. Các câu hỏi thường gặp như giờ mở cửa, chi nhánh, mã giảm giá hay cách đặt món được trả lời ngay theo bộ từ khoá do quản trị viên soạn, kèm nút dẫn tới trang liên quan. Câu hỏi khác được chuyển tới dịch vụ Google Gemini cùng danh sách món và giá đang bán tại chi nhánh, nhờ đó câu trả lời không bịa món hay giá. Trợ lý chỉ gợi ý khách quan, luôn nhắc giá có thể thay đổi, và mỗi khách gửi tối đa 10 tin nhắn mỗi phút.

**Hỗ trợ và liên hệ:** Trang hỗ trợ cung cấp số điện thoại đường dây nóng, hộp thư điện tử và địa chỉ các chi nhánh. Cuối trang chủ có biểu mẫu liên hệ, khách vãng lai cũng gửi được; tin nhắn được chuyển tới trang quản trị để xử lý.

**b. Phân hệ Quản lý chi nhánh**

**Bảng tổng quan:** Ngay sau khi đăng nhập, quản lý thấy tình hình kinh doanh của chi nhánh qua các thẻ số đếm đơn theo từng trạng thái và doanh thu, cùng biểu đồ đơn hàng và doanh thu bảy ngày gần nhất. Khu vực "Đơn chờ xác nhận" cho phép duyệt hoặc huỷ đơn mới ngay tại chỗ. Quản lý cũng chọn tối đa mười món "Ưu đãi đặc biệt từ đầu bếp" để hiển thị trên trang chủ của chi nhánh.

**Xử lý đơn hàng:** Quản lý là người điều phối toàn bộ vòng đời đơn hàng theo trình tự chờ xác nhận, đã xác nhận, đang nấu, sẵn sàng và hoàn thành. Thao tác huỷ chỉ được phép khi đơn chưa đưa vào chế biến. Mọi lần chuyển trạng thái đều được ghi nhật ký gồm người thực hiện, trạng thái trước, trạng thái sau và ghi chú kèm theo.

**Thực đơn và giá bán của chi nhánh:** Quản lý quyết định món nào được bày bán tại chi nhánh, đặt giá riêng cho từng món, cập nhật số lượng tồn, đánh dấu còn hoặc hết món và gắn nhãn Bán chạy; toàn bộ bảng được lưu bằng một thao tác để thực đơn hiển thị cho khách luôn khớp với thực tế tại quầy.

**Nhân sự:** Quản lý khởi tạo tài khoản cho nhân viên và đầu bếp thuộc chi nhánh, chỉ được chọn trong hai vai trò này. Ngoài ra, quản lý khoá hoặc mở lại tài khoản, đặt lại mật khẩu, thiết lập đơn giá lương theo giờ và mức lương áp dụng trong thời gian thử việc.

**Ca làm việc:** Danh mục ca làm được khai báo linh hoạt theo đặc thù từng chi nhánh, ví dụ ca sáng từ 07:00 đến 11:30, ca trưa từ 11:30 đến 17:00 và ca tối từ 17:00 đến 22:00. Quản lý có thể sửa giờ của ca đang dùng, thêm ca mới và theo dõi số lượt chấm công đã phát sinh trên từng ca.

**Xếp lịch làm:** Ngay trên trang ca làm, quản lý xếp lịch theo tuần dưới dạng bảng: mỗi hàng là một nhân viên hoặc đầu bếp đang làm việc, mỗi cột là một ngày trong tuần. Quản lý đánh dấu một hoặc nhiều ca cho từng ngày rồi lưu cả tuần một lần; lịch tuần được kết xuất ra Excel để in và dán tại quầy.

**Thiết bị chấm công tại quầy:** Một máy tính hoặc máy tính bảng có webcam đặt tại quầy được ghép với chi nhánh thông qua đường liên kết do quản lý tạo ra. Quản lý đăng ký khuôn mặt cho từng nhân sự bằng cách cho trang tự chụp 15 mẫu nhìn thẳng, hoặc tải lên từ 3 đến 15 ảnh chân dung; ảnh tải lên chỉ được xử lý trên trình duyệt, máy chủ chỉ lưu đặc trưng khuôn mặt. Khi không còn dùng, quản lý thu hồi thiết bị để vô hiệu hoá liên kết.

**Chấm công:** Quản lý tra cứu bảng chấm công theo từng ngày với các thông tin nhân viên, ca làm, giờ vào, giờ ra, số giờ làm và hình thức chấm công. Trường hợp thiết bị gặp sự cố hoặc nhân sự quên chấm, quản lý thực hiện chấm công thủ công bằng cách chọn nhân viên, chọn ca, nhập giờ vào, giờ ra kèm ghi chú giải trình. Dữ liệu chấm công theo ngày được kết xuất ra tập tin Excel khi cần lưu trữ hoặc đối chiếu.

**Bảng lương:** Hệ thống tính lương theo số giờ làm thực tế nhân với đơn giá theo giờ của từng nhân sự, tự động áp dụng mức lương thử việc trong bảy ngày đầu. Quản lý ghi từng khoản thưởng hoặc phạt kèm lý do, có thể sửa hoặc xoá khi bảng lương còn ở trạng thái nháp; tổng thưởng và tổng phạt được cộng tự động. Bảng lương được chốt qua ba bước nháp, đã chốt, đã trả rồi xuất ra Excel để lưu hồ sơ.

**Báo cáo:** Báo cáo doanh thu tổng hợp số đơn và số tiền theo ngày, tháng hoặc năm, chia theo hình thức nhận hàng, phương thức thanh toán và nhóm món bán chạy, có biểu đồ và xuất Excel. Mục đánh giá tập hợp phản hồi của khách hàng để quản lý xem xét và trả lời.

**c. Phân hệ Quản trị viên hệ thống**

Quản trị viên giữ quyền cao nhất trên toàn chuỗi và làm việc trên khu vực quản trị riêng. Quản trị viên quản lý chi nhánh, tài khoản quản lý, nhân viên và khách hàng; danh mục và món ăn; banner trang chủ; mã giảm giá và việc gửi thông báo mã tới khách hàng; cài đặt chung như thông tin liên hệ và phương thức thanh toán đang bật; bộ từ khoá của chatbot; tin nhắn liên hệ từ trang chủ; theo dõi đơn hàng, chấm công, bảng lương, báo cáo toàn chuỗi và nhật ký thao tác. Ngoài giao diện web, tài khoản quản lý còn có thể được tạo bằng câu lệnh quản trị trên máy chủ.

**d. Phân hệ Nhân viên**

Nhân viên sử dụng tài khoản do quản lý cấp để định danh trên hệ thống. Nghiệp vụ chính của nhóm người dùng này là chấm công tại thiết bị đặt ở quầy: chọn thao tác chấm vào, chấm ra hoặc ra ca sớm, sau đó nhìn vào camera để hệ thống xác thực. Khi đi trễ quá năm phút hoặc ra ca sớm hơn mười phút so với giờ ca, nhân viên phải nhập lý do ngay trên màn hình để quản lý xem xét. Sau khi đăng nhập, nhân viên vào trang "Của tôi" để xem lịch làm được xếp trong tháng (có thể xuất ra Excel), lịch sử chấm công kèm số phút đi trễ hoặc ra sớm và bảng lương tháng của chính mình.

**e. Phân hệ Đầu bếp**

Đầu bếp cũng được cấp tài khoản riêng và thực hiện chấm công theo cùng cơ chế nhận diện khuôn mặt như nhân viên. Việc tách riêng vai trò đầu bếp nhằm phục vụ hai mục tiêu: tính lương đúng theo giờ công của bộ phận chế biến và cung cấp màn hình bếp riêng. Màn hình này hiển thị các đơn đã xác nhận và đang nấu của chi nhánh với cỡ chữ lớn, tự làm mới sau mỗi 30 giây; đầu bếp chỉ theo dõi, việc chuyển trạng thái đơn vẫn do quản lý thực hiện. Ngoài ra, đầu bếp dùng trang "Của tôi" giống nhân viên để xem lịch làm, chấm công và lương của mình.

## 1.2. PHÂN CÔNG CÔNG VIỆC VÀ TIẾN ĐỘ THỰC HIỆN

### 1.2.1. Phân công công việc

*Bảng* 1*: Phân công công việc.*

| **STT** | **MSSV** | **Họ tên** | **Công việc thực hiện** |
|:--:|----|----|----|
| 1 | 023101006 | Ngô Hữu Tính | Thiết kế giao diện phía khách hàng (thực đơn, chính sách, thông báo); thiết kế dữ liệu đơn hàng và mã giảm giá; xây dựng đặt hàng, phí giao hàng, mã giảm giá theo phạm vi, theo dõi, đặt lại và đánh giá đơn. |
| 2 | 023101057 | Cao Phúc Thịnh | Xây dựng phân hệ quản lý chi nhánh (tổng quan, đơn hàng, thực đơn, nhân sự, ca làm, bảng lương, báo cáo); chấm công bằng nhận diện khuôn mặt; khu vực quản trị viên; chatbot, tìm kiếm; kiểm thử tự động. |
| 3 | 023101058 | Nguyễn Văn Trực | Phân tích yêu cầu, vẽ sơ đồ use case; xây dựng phiên bản đầu của trang quản trị viên; tổng hợp báo cáo. |

### 1.2.2. Tiến trình thực hiện

*Bảng* 2*: Tiến trình thực hiện.*

<table style="width:97%;">
<colgroup>
<col style="width: 15%" />
<col style="width: 15%" />
<col style="width: 50%" />
<col style="width: 16%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Giai đoạn</strong></p></th>
<th style="text-align: center;"><p><strong>Thời gian</strong></p></th>
<th style="text-align: center;"><p><strong>Nội dung công việc</strong></p></th>
<th style="text-align: center;"><p><strong>Ghi chú</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p>Khởi tạo và phân tích</p></td>
<td style="text-align: center;"><p>Tuần 1 – 2</p></td>
<td style="text-align: left;"><p>Nhận đề tài, họp nhóm thống nhất phạm vi. Khảo sát quy trình bán hàng của một số cửa hàng thức ăn nhanh và các website đặt món hiện có. Xác định năm tác nhân, thống kê nghiệp vụ cần số hoá (đơn hàng, thực đơn theo chi nhánh, ca làm, chấm công, lương).</p></td>
<td style="text-align: center;"></td>
</tr>
<tr>
<td style="text-align: center;"><p>Thiết kế hệ thống</p></td>
<td style="text-align: center;"><p>Tuần 3 – 4</p></td>
<td style="text-align: left;"><p>Xây dựng sơ đồ use case, sơ đồ lớp và sơ đồ quan hệ cơ sở dữ liệu. Thiết kế bố cục giao diện cho hai nhóm màn hình: trang bán hàng cho khách và khu vực quản trị chi nhánh.</p></td>
<td style="text-align: center;"></td>
</tr>
<tr>
<td rowspan="2" style="text-align: left;"><p>Xây dựng phân hệ quản lý chi nhánh</p>
<p>Xây dựng phân hệ khách hàng</p></td>
<td style="text-align: center;"><p>Tuần 5 – 7</p></td>
<td style="text-align: left;"><p>Lập trình đăng nhập và phân quyền theo vai trò. Xây dựng màn hình tổng quan, xử lý đơn hàng theo luồng trạng thái, quản lý thực đơn và giá bán theo chi nhánh.</p></td>
<td style="text-align: center;"></td>
</tr>
<tr>
<td style="text-align: center;"><p>Tuần 8 – 10</p></td>
<td style="text-align: left;"><p>Dựng giao diện trang chủ, thực đơn theo chi nhánh, trang chi tiết món. Hoàn thiện giỏ hàng, đặt hàng, thanh toán, hồ sơ cá nhân và theo dõi đơn.</p></td>
<td style="text-align: center;"></td>
</tr>
<tr>
<td style="text-align: center;"><p>Xây dựng nghiệp vụ nhân sự</p></td>
<td style="text-align: center;"><p>Tuần 11 – 12</p></td>
<td style="text-align: left;"><p>Lập trình quản lý nhân viên, ca làm việc, ghép thiết bị quầy, chấm công bằng nhận diện khuôn mặt, chấm công thủ công, tính lương và kết xuất Excel.</p></td>
<td style="text-align: center;"></td>
</tr>
<tr>
<td rowspan="2" style="text-align: left;"><p>Tích hợp trợ lý AI và báo cáo</p>
<p>Kiểm thử và hoàn thiện</p></td>
<td style="text-align: center;"><p>Tuần 13</p></td>
<td style="text-align: left;"><p>Xây dựng khung hội thoại tư vấn thực đơn dựa trên số liệu bán hàng. Hoàn thiện báo cáo doanh thu và báo cáo đánh giá.</p></td>
<td style="text-align: center;"></td>
</tr>
<tr>
<td style="text-align: center;"><p>Tuần 14 – 15</p></td>
<td style="text-align: left;"><p>Kiểm thử theo kịch bản: luồng trạng thái đơn, điều kiện huỷ đơn, chấm công đi trễ và ra ca sớm, tính giờ làm và bảng lương. Sửa lỗi và nhập dữ liệu mẫu.</p></td>
<td style="text-align: center;"></td>
</tr>
</tbody>
</table>

# CHƯƠNG 2: PHÂN TÍCH, THIẾT KẾ VÀ XÂY DỰNG PHẦN MỀM

## 2.1. PHÂN TÍCH

### 2.1.1. Bối cảnh thực tiễn và tính cấp thiết của đề tài

Trong lĩnh vực dịch vụ ăn uống, mô hình thức ăn nhanh dạng chuỗi có đặc điểm là thời gian phục vụ ngắn, số lượng đơn phát sinh dày trong các giờ cao điểm và nhân sự thường làm việc theo ca. Những đặc điểm này khiến công tác quản lý không chỉ dừng ở việc bán hàng, mà còn liên quan trực tiếp đến điều phối chế biến, kiểm soát giờ công và tính lương cho nhiều nhóm nhân sự khác nhau tại từng điểm bán.

Qua khảo sát, nhóm nhận thấy phần lớn giải pháp mà các cửa hàng nhỏ đang dùng chỉ giải quyết được một nửa bài toán. Các nền tảng giao đồ ăn trung gian giúp tiếp cận khách hàng nhưng thu phí hoa hồng, đồng thời dữ liệu khách hàng và lịch sử đơn hàng không thuộc quyền kiểm soát của cửa hàng. Ở chiều ngược lại, khâu vận hành nội bộ như phân ca, theo dõi giờ công, tính lương và đối soát doanh thu phần lớn vẫn được ghi chép trên sổ tay hoặc bảng tính rời, dẫn đến sai lệch số liệu và mất nhiều thời gian tổng hợp vào cuối kỳ.

Riêng khâu chấm công, các hình thức truyền thống đều bộc lộ hạn chế trong môi trường nhà hàng. Thẻ từ dễ bị quên hoặc cho người khác quẹt hộ, còn máy quét vân tay thường nhận dạng kém khi tay nhân sự bếp ẩm hoặc có dầu mỡ. Việc dùng camera của thiết bị đặt tại quầy để nhận diện khuôn mặt, so khớp với bộ mẫu đã đăng ký và lưu lại ảnh làm bằng chứng cho mỗi lượt chấm công, vừa khắc phục được các hạn chế nêu trên vừa không cần thêm thiết bị chuyên dụng.

Từ bối cảnh đó, đề tài "Xây dựng web app cửa hàng bán đồ ăn nhanh – ZomZop" hướng đến giải quyết đồng thời hai nhu cầu: tạo kênh bán hàng trực tuyến do chính cửa hàng làm chủ và cung cấp một môi trường quản trị khép kín cho từng chi nhánh, bao gồm bán hàng, nhân sự, giờ công, lương và báo cáo. Bên cạnh đó, hệ thống bố trí trợ lý chatbot dựa trên thực đơn và giá thật của chi nhánh, giúp khách hàng chọn món nhanh hơn.

### 2.1.2. Phương pháp và cách thức giải quyết bài toán

Do hệ thống có nhiều nhóm nghiệp vụ khác biệt nhau, nhóm không triển khai một lần toàn bộ mà chia thành các vòng phát triển ngắn, mỗi vòng hoàn thiện một nhóm chức năng rồi kiểm thử trước khi chuyển sang nhóm tiếp theo. Cách làm này giúp phát hiện sớm những điểm chưa hợp lý trong thiết kế dữ liệu, nhất là ở nghiệp vụ chấm công và tính lương, bao gồm các bước:

**Bước 1: Khảo sát và chuẩn hoá nghiệp vụ.** Nhóm mô tả lại quy trình thực tế tại quán ăn nhanh theo trình tự tiếp nhận đơn, xác nhận, chuyển bếp, hoàn tất chế biến và giao cho khách; đồng thời chuẩn hoá quy tắc phân ca, quy định về thời gian cho phép đi trễ hoặc ra ca sớm và cách tính lương theo giờ.

**Bước 2: Thiết kế kiến trúc và cơ sở dữ liệu.** Cơ sở dữ liệu được thiết kế xoay quanh thực thể chi nhánh, vì gần như mọi dữ liệu nghiệp vụ đều gắn với một chi nhánh cụ thể: thực đơn và giá bán, đơn hàng, nhân sự, ca làm, lượt chấm công, bảng lương và đánh giá. Mô hình dữ liệu đồng thời dự trù các bảng phục vụ lưu vết lịch sử đơn hàng, lưu đặc trưng khuôn mặt, quản lý mã giảm giá và lưu phiên hội thoại của trợ lý AI.

**Bước 3: Lựa chọn công nghệ và lập trình.** Phần xử lý nghiệp vụ được xây dựng trên nền Laravel theo mô hình MVC, trong đó cơ chế phân quyền theo vai trò được đặt ở lớp trung gian để ngăn truy cập chéo giữa các chi nhánh. Phần nhận diện khuôn mặt được xử lý trên trình duyệt của thiết bị tại quầy nhằm giảm tải cho máy chủ và tránh truyền hình ảnh thô qua mạng.

**Bước 4: Kiểm thử và tinh chỉnh.** Nhóm xây dựng các kịch bản kiểm thử cho những luồng dễ phát sinh sai sót, gồm: chuyển trạng thái đơn không đúng thứ tự, huỷ đơn khi đơn đã vào bếp, chấm công khi đi trễ hoặc ra sớm, chấm ra khi chưa có bản ghi chấm vào và tính giờ công cho ca làm việc kết thúc muộn. Kết quả kiểm thử được dùng để điều chỉnh lại các ràng buộc ở tầng xử lý.

### 2.1.3. Quy trình hoạt động của hệ thống

**a. Quy trình phía Khách hàng**

Khách hàng truy cập trang bán hàng, chọn chi nhánh phù hợp rồi tham khảo thực đơn, tìm món hoặc xem mục Bán chạy. Trong quá trình chọn món, khách hàng có thể hỏi trợ lý chatbot về món ăn, giá hoặc nhờ gợi ý phần ăn phù hợp.

Sau khi hoàn tất giỏ hàng, khách hàng chọn hình thức nhận hàng; nếu giao tận nơi thì chọn địa chỉ trong sổ địa chỉ và tốc độ giao. Khách hàng áp dụng mã giảm giá, chọn phương thức thanh toán và xác nhận tạo đơn.

Đơn mới được ghi nhận ở trạng thái chờ xác nhận và hiển thị ngay trên màn hình tổng quan của chi nhánh. Khách hàng theo dõi tiến trình xử lý trong mục đơn hàng của tài khoản; khi đơn hoàn thành, khách hàng gửi đánh giá về món ăn và khâu giao hàng, hoặc đặt lại đơn cũ chỉ với vài thao tác.

**b. Quy trình phía Quản lý chi nhánh**

Quản lý bắt đầu ngày làm việc từ màn hình tổng quan để nắm số lượng đơn đang tồn ở từng trạng thái và doanh thu đã phát sinh. Với mỗi đơn mới, quản lý kiểm tra lại món, địa chỉ và hình thức nhận hàng rồi quyết định duyệt hoặc huỷ.

Đơn được duyệt sẽ lần lượt chuyển sang đang nấu khi bếp bắt đầu chế biến, sẵn sàng khi món đã xong và hoàn thành khi khách đã nhận. Song song với công việc bán hàng, quản lý cập nhật tình trạng còn hoặc hết món để thực đơn hiển thị đúng thực tế, bố trí ca làm cho nhân sự, kiểm tra bảng chấm công trong ngày và xử lý các trường hợp cần chấm công thủ công.

Cuối kỳ, quản lý rà soát giờ công, ghi các khoản thưởng và phạt kèm lý do, chốt bảng lương rồi kết xuất ra Excel; đồng thời xem báo cáo doanh thu và trả lời những đánh giá mà khách hàng gửi cho chi nhánh.

**c. Quy trình phía Quản trị viên hệ thống**

Quản trị viên chuẩn bị dữ liệu nền cho toàn chuỗi gồm chi nhánh, danh mục và thực đơn gốc, banner và mã giảm giá, sau đó tạo tài khoản quản lý cho từng chi nhánh trên trang quản trị. Trong quá trình vận hành, quản trị viên cập nhật cài đặt chung, soạn từ khoá cho chatbot, xử lý tin nhắn liên hệ và theo dõi số liệu của toàn chuỗi.

**d. Quy trình phía Nhân viên và Đầu bếp**

Vào đầu ca, nhân sự đến thiết bị chấm công đặt tại quầy, bấm nút chấm vào và nhìn thẳng vào camera. Trình duyệt trích xuất đặc trưng khuôn mặt, chỉ nhận khung hình mắt mở và nhìn thẳng, so khớp với bộ mẫu đã đăng ký rồi gửi kết quả cùng ảnh chụp lên máy chủ để lưu kèm lượt chấm công.

Nếu thời điểm chấm vào trễ hơn giờ ca quá năm phút, hệ thống yêu cầu nhập lý do. Khi kết thúc ca, nhân sự thực hiện thao tác chấm ra; trường hợp cần về trước giờ, nhân sự chọn thao tác ra ca sớm và cũng phải giải trình nếu sớm hơn mười phút. Toàn bộ lượt chấm công được ghi lại cùng hình thức chấm để quản lý đối chiếu khi tính lương.

### 2.1.4. Cơ sở lý thuyết và công nghệ sử dụng

Hệ thống ZomZop được xây dựng theo mô hình khách – chủ, kết hợp các công nghệ và thư viện sau:

\- **Laravel 13 & PHP 8.3:** Framework PHP theo kiến trúc MVC, cung cấp ORM Eloquent để làm việc với cơ sở dữ liệu, định tuyến, middleware phân quyền theo vai trò, kiểm tra dữ liệu đầu vào và chống tấn công CSRF.

\- **MySQL & Laragon:** MySQL lưu trữ dữ liệu nghiệp vụ, các thao tác quan trọng như đặt hàng, dùng mã giảm giá và chỉnh bảng lương được thực hiện trong giao dịch (transaction) để bảo đảm toàn vẹn. Laragon dùng để dựng máy chủ cục bộ trong quá trình phát triển.

\- **Tailwind CSS & Vite:** Tailwind CSS giúp xây dựng giao diện đồng nhất, thích ứng nhiều kích thước màn hình; Vite đóng gói và tối ưu tài nguyên giao diện.

\- **face-api.js:** Thư viện JavaScript \[11\] nhận diện khuôn mặt chạy ngay trên trình duyệt. Mỗi khuôn mặt được biểu diễn bằng vectơ đặc trưng 128 chiều; hai khuôn mặt được coi là cùng một người khi khoảng cách Euclid giữa hai vectơ nhỏ hơn ngưỡng cấu hình, độ tương đồng hiển thị bằng (1 − khoảng cách) × 100%.

\- **OpenSpout, PhpSpreadsheet & ApexCharts:** Hai thư viện PHP dùng để xuất báo cáo doanh thu, bảng chấm công và bảng lương ra tập tin Excel; ApexCharts \[13\] vẽ biểu đồ trên trang tổng quan và báo cáo.

\- **Google Gemini API:** Dịch vụ mô hình ngôn ngữ lớn của Google \[12\], được dùng để trả lời các câu hỏi của khách hàng mà bộ từ khoá chưa bao quát; hệ thống gửi kèm danh sách món và giá của chi nhánh để câu trả lời bám sát thực đơn.

\- **Quản lý mã nguồn:** Dự án dùng Git, lưu trữ trên GitHub để kiểm soát phiên bản và phối hợp giữa các thành viên; các nghiệp vụ chính được kiểm thử tự động bằng PHPUnit.

### 2.1.5. Phân tích bài toán

**a. Các tác nhân và nguyên tắc phân quyền**

Hệ thống phân biệt năm vai trò, được lưu trực tiếp trong thuộc tính vai trò của bảng người dùng. Nguyên tắc chung là quyền hạn thu hẹp dần theo phạm vi dữ liệu mà người dùng được phép tác động.

Quản trị viên hệ thống nắm quyền cao nhất trên toàn chuỗi, quản lý dữ liệu nền và cấp tài khoản quản lý chi nhánh trên trang quản trị hoặc bằng câu lệnh quản trị trên máy chủ.

Quản lý chi nhánh là tác nhân trung tâm của khâu vận hành, nhưng phạm vi tác động bị giới hạn trong đúng chi nhánh được phân công. Quản lý xử lý đơn hàng, điều chỉnh thực đơn và giá bán, quản lý nhân sự, ca làm, thiết bị chấm công, bảng lương, đồng thời theo dõi báo cáo và phản hồi đánh giá. Toàn bộ chức năng này nằm trong khu vực quản trị riêng, chỉ tài khoản có vai trò quản lý mới truy cập được.

Nhân viên và Đầu bếp là tài khoản do quản lý khởi tạo. Hai vai trò này không can thiệp vào dữ liệu bán hàng; nghiệp vụ chính của họ là định danh trên hệ thống và chấm công theo ca. Việc tách hai vai trò nhằm phân biệt bộ phận phục vụ và bộ phận chế biến khi bố trí ca cũng như khi tính lương.

Khách hàng là người dùng cuối, tự đăng ký tài khoản để đặt món, thanh toán, theo dõi đơn, gửi đánh giá và trò chuyện với trợ lý chatbot. Khách chưa đăng nhập vẫn xem thực đơn, tìm món, thêm món vào giỏ và gửi tin nhắn liên hệ được.

**b. Các nghiệp vụ trọng tâm**

**Nghiệp vụ xử lý đơn hàng:** Đơn hàng được quản lý theo sáu trạng thái, trong đó năm trạng thái tạo thành một chuỗi tuần tự từ chờ xác nhận đến hoàn thành, còn trạng thái đã huỷ là nhánh rẽ chỉ hợp lệ khi đơn chưa đưa vào chế biến. Quy tắc này nhằm tránh tình trạng huỷ đơn khi nguyên liệu đã được sử dụng. Mỗi lần chuyển trạng thái, hệ thống ghi một dòng nhật ký gồm người thực hiện, trạng thái trước, trạng thái sau, thời điểm và ghi chú, nhờ đó mọi tranh chấp về đơn hàng đều có căn cứ để đối chiếu.

**Nghiệp vụ thực đơn theo chi nhánh:** Món ăn được khai báo một lần ở cấp hệ thống, nhưng giá bán, số lượng tồn và tình trạng còn hoặc hết món lại được thiết lập riêng cho từng chi nhánh. Thiết kế này phản ánh đúng thực tế là cùng một món có thể có giá khác nhau giữa các điểm bán và không phải chi nhánh nào cũng bán đủ mọi món.

**Nghiệp vụ chấm công bằng nhận diện khuôn mặt:** Quy trình gồm ba phần. Trước tiên, quản lý ghép thiết bị tại quầy với chi nhánh thông qua đường liên kết chỉ hiển thị một lần khi tạo và có thể thu hồi bất cứ lúc nào và đăng ký khuôn mặt cho từng nhân sự bằng 15 mẫu tự chụp hoặc 3 đến 15 ảnh tải lên; hệ thống kiểm tra các mẫu thuộc cùng một người trước khi lưu. Tiếp đó, nhân sự tự chấm công tại thiết bị bằng cách nhìn thẳng vào camera; hệ thống so khớp đặc trưng khuôn mặt, ghi độ tương đồng, lưu ảnh minh chứng trong ba mươi ngày và yêu cầu nhập lý do nếu đi trễ quá năm phút hoặc ra ca sớm quá mười phút. Cuối cùng, quản lý rà soát bảng chấm công theo ngày, bổ sung bản ghi thủ công cho các trường hợp ngoại lệ và kết xuất số liệu ra Excel.

**Nghiệp vụ tính lương:** Lương được tính trên số giờ làm thực tế giữa thời điểm chấm vào và chấm ra, nhân với đơn giá theo giờ của từng nhân sự; trong bảy ngày đầu kể từ khi nhận việc, hệ thống áp dụng đơn giá thử việc. Thưởng và phạt được ghi thành từng khoản có lý do để dễ đối chiếu. Bảng lương đi qua ba trạng thái nháp, đã chốt và đã trả, chỉ được chuyển theo một chiều để bảo đảm số liệu đã chi trả không bị sửa lại.

**Nghiệp vụ mã giảm giá:** Hệ thống hỗ trợ giảm theo tỉ lệ phần trăm và giảm theo số tiền cố định. Mỗi mã có phạm vi áp dụng là tiền món, phí giao tiêu chuẩn hoặc phí giao hỏa tốc; kèm điều kiện giá trị đơn tối thiểu, tổng số lượt, số lượt cho mỗi khách, ngày bắt đầu và ngày hết hạn. Lượt dùng được ghi nhận trong cùng giao dịch với việc tạo đơn và được hoàn lại khi đơn bị huỷ; quản trị viên có thể gửi thông báo mã tới khách hàng.

**Nghiệp vụ tư vấn thực đơn:** Trợ lý chatbot ưu tiên trả lời theo bộ từ khoá do quản trị viên soạn; nếu không khớp từ khoá nào, câu hỏi cùng danh sách món và giá đang bán tại chi nhánh được gửi tới Google Gemini. Phiên hội thoại được lưu cho từng khách hàng để trợ lý hiểu ngữ cảnh gần nhất, đồng thời hệ thống giới hạn tần suất gửi tin để tránh lạm dụng.

**c. Giới hạn phạm vi của đề tài**

Trong khuôn khổ một đồ án học phần, nhóm xác định rõ những nội dung chưa đưa vào phạm vi triển khai. Hệ thống chưa kết nối tới cổng thanh toán điện tử, vì vậy với lựa chọn MoMo hoặc VNPay, phần mềm chỉ ghi nhận phương thức mà khách đã chọn để phục vụ đối soát. Riêng hình thức chuyển khoản, hệ thống tạo mã VietQR kèm sẵn số tiền và nội dung chuyển khoản, nhưng việc xác nhận tiền về vẫn do quản lý thực hiện thủ công. Phí giao hàng được tính theo mức cố định, chưa tính theo khoảng cách và chưa có chức năng điều phối người giao hàng. Hệ thống không quản lý nguyên liệu đầu vào, số lượng tồn chỉ được theo dõi ở mức món ăn. Ngoài ra, chức năng dự báo doanh số và tiếp nhận phiếu hỗ trợ trực tuyến mới dừng ở phần thiết kế dữ liệu, được nhóm xếp vào định hướng phát triển trình bày ở Chương 3.

### 2.1.6. Yêu cầu phần mềm

**a. Các chức năng chính**

*Bảng* 3*: Các chức năng chính của hệ thống.*

| **STT** | **Tên chức năng** | **Mô tả chi tiết** |
|:--:|----|----|
| 1 | Đăng ký, đăng nhập và đăng xuất | Khách hàng tự tạo tài khoản hoặc đăng nhập bằng Google, Facebook; các tác nhân còn lại sử dụng tài khoản được cấp. Sau khi xác thực, hệ thống căn cứ vai trò để điều hướng người dùng đến đúng khu vực làm việc. |
| 2 | Tra cứu thực đơn và đặt hàng | Khách hàng chọn chi nhánh, tìm món ngay khi gõ hoặc lọc theo danh mục, xem món bán chạy, thêm vào giỏ kèm ghi chú và tạo đơn mang đi hoặc giao tận nơi (chọn địa chỉ trong sổ địa chỉ, tốc độ giao tiêu chuẩn hoặc hỏa tốc). |
| 3 | Thanh toán đơn hàng | Khách hàng áp dụng mã giảm giá (tiền món, phí giao tiêu chuẩn hoặc hỏa tốc) và chọn thanh toán bằng tiền mặt, chuyển khoản VietQR, MoMo hoặc VNPay. Tổng tiền = tiền món + phí giao − giảm giá. Đơn tiền mặt được ghi nhận đã thanh toán khi hoàn thành. |
| 4 | Quản lý hồ sơ và theo dõi đơn hàng | Khách hàng cập nhật hồ sơ, sổ địa chỉ, món yêu thích, kho mã giảm giá; theo dõi trạng thái và lịch sử từng đơn, đặt lại đơn cũ theo giá hiện hành. |
| 5 | Đánh giá đơn hàng | Khách hàng chấm sao cho đơn hàng, chấm sao riêng cho khâu giao hàng và để lại nhận xét sau khi đơn hoàn thành. |
| 6 | Tư vấn thực đơn bằng trợ lý AI | Khách hàng đã đăng nhập hỏi về món ăn, giá, cách đặt món; trợ lý trả lời theo từ khoá do quản trị viên soạn hoặc qua Google Gemini dựa trên thực đơn và giá thật của chi nhánh. |
| 7 | Xử lý đơn hàng chi nhánh | Quản lý duyệt hoặc huỷ đơn mới, cập nhật tiến trình chế biến và giao nhận theo đúng trình tự trạng thái, đồng thời lưu nhật ký cho từng lần chuyển trạng thái. |
| 8 | Quản lý thực đơn và giá bán chi nhánh | Quản lý chọn món bày bán tại chi nhánh, đặt giá riêng, cập nhật số lượng tồn, đánh dấu còn hoặc hết món, gắn nhãn Bán chạy và chọn món đề xuất trên trang chủ. |
| 9 | Quản lý nhân sự và ca làm việc | Quản lý tạo tài khoản nhân viên, đầu bếp, khoá hoặc mở tài khoản, đặt lại mật khẩu, thiết lập đơn giá lương; khai báo và điều chỉnh danh mục ca làm của chi nhánh; xếp lịch làm theo tuần và kết xuất lịch ra Excel. |
| 10 | Chấm công bằng nhận diện khuôn mặt | Quản lý ghép và thu hồi thiết bị tại quầy, đăng ký khuôn mặt (15 mẫu tự chụp hoặc 3–15 ảnh tải lên); nhân viên và đầu bếp chấm vào, chấm ra hoặc ra ca sớm bằng cách nhìn thẳng vào camera; quản lý chấm công thủ công khi cần. |
| 11 | Quản lý bảng lương | Hệ thống tính lương theo giờ công thực tế; quản lý ghi từng khoản thưởng, phạt kèm lý do, chốt bảng lương theo ba bước và kết xuất ra Excel. |
| 12 | Báo cáo doanh thu | Tổng hợp số đơn, doanh thu theo khoảng thời gian, theo phương thức thanh toán và theo nhóm món bán chạy của chi nhánh. |
| 13 | Quản lý đánh giá của khách hàng | Quản lý xem danh sách đánh giá dành cho chi nhánh, xem chi tiết nhận xét và gửi phản hồi tới khách hàng. |
| 14 | Quản trị hệ thống và cấp phát tài khoản quản lý | Quản trị viên quản lý chi nhánh, tài khoản quản lý, thực đơn gốc, banner, mã giảm giá, cài đặt chung, từ khoá chatbot, tin nhắn liên hệ và theo dõi số liệu toàn chuỗi. |

**b. Mối liên hệ giữa người dùng và chức năng**

*Bảng* 4*: Mối liên hệ giữa người dùng và chức năng.*

| **STT** | **Tên chức năng** | **Người dùng** |
|:--:|----|----|
| 1 | Đăng ký, đăng nhập và đăng xuất | Khách hàng, Nhân viên, Đầu bếp, Quản lý chi nhánh, Quản trị viên hệ thống |
| 2 | Tra cứu thực đơn và đặt hàng | Khách hàng |
| 3 | Thanh toán đơn hàng | Khách hàng |
| 4 | Quản lý hồ sơ và theo dõi đơn hàng | Khách hàng |
| 5 | Đánh giá đơn hàng | Khách hàng |
| 6 | Tư vấn thực đơn bằng trợ lý AI | Khách hàng |
| 7 | Xử lý đơn hàng chi nhánh | Quản lý chi nhánh |
| 8 | Quản lý thực đơn và giá bán chi nhánh | Quản lý chi nhánh |
| 9 | Quản lý nhân sự và ca làm việc | Quản lý chi nhánh |
| 10 | Chấm công bằng nhận diện khuôn mặt | Nhân viên, Đầu bếp, Quản lý chi nhánh |
| 11 | Quản lý bảng lương | Quản lý chi nhánh |
| 12 | Báo cáo doanh thu | Quản lý chi nhánh |
| 13 | Quản lý đánh giá của khách hàng | Quản lý chi nhánh |
| 14 | Quản trị hệ thống và cấp phát tài khoản quản lý | Quản trị viên hệ thống |

**c. Các yêu cầu chức năng của hệ thống**

Dưới đây là sự phân rã chi tiết cho từng chức năng cốt lõi, tập trung vào bốn khía cạnh: Lưu trữ, Tìm kiếm, Thống kê - kết xuất và Tính toán - xử lý.

**- Đăng ký, đăng nhập và đăng xuất**

> \+ Lưu trữ: Thông tin tài khoản gồm họ tên, số điện thoại, hộp thư điện tử, mật khẩu đã mã hoá, vai trò và chi nhánh trực thuộc đối với nhân sự.
>
> \+ Tìm kiếm: Đối chiếu số điện thoại hoặc hộp thư để phát hiện tài khoản trùng khi đăng ký; truy vấn tài khoản tương ứng khi xác thực đăng nhập.
>
> \+ Thống kê, kết xuất: Không.
>
> \+ Tính toán, xử lý: Mã hoá mật khẩu trước khi lưu, kiểm tra trạng thái khoá của tài khoản và điều hướng người dùng theo vai trò sau khi xác thực.

**- Tra cứu thực đơn và đặt hàng**

> \+ Lưu trữ: Giỏ hàng tạm thời theo phiên và đơn hàng chính thức, gồm chi nhánh phục vụ, danh sách món, số lượng, đơn giá tại thời điểm đặt, hình thức nhận hàng, tốc độ giao, phí giao hàng và bản sao địa chỉ nhận hàng.
>
> \+ Tìm kiếm: Tìm món theo tên, lọc theo danh mục, tra cứu giá bán và tình trạng còn món theo chi nhánh đã chọn.
>
> \+ Thống kê, kết xuất: Không.
>
> \+ Tính toán, xử lý: Kiểm tra lại giá và tình trạng món với cơ sở dữ liệu; tính tiền món, phí giao hàng, số tiền giảm và tổng tiền; sinh mã đơn; tạo đơn, ghi lượt dùng mã và nhật ký trạng thái đầu tiên trong cùng một giao dịch.

**- Thanh toán đơn hàng**

> \+ Lưu trữ: Phương thức thanh toán và trạng thái thanh toán gắn với đơn hàng.
>
> \+ Tìm kiếm: Tra cứu đơn cần thanh toán theo mã đơn.
>
> \+ Thống kê, kết xuất: Cung cấp số liệu cho báo cáo doanh thu phân theo phương thức thanh toán.
>
> \+ Tính toán, xử lý: Tự động chuyển trạng thái thanh toán của đơn tiền mặt sang đã thanh toán khi đơn hoàn thành; với đơn chuyển khoản, quản lý xác nhận đã nhận tiền.

**- Quản lý hồ sơ và theo dõi đơn hàng**

> \+ Lưu trữ: Thông tin hồ sơ, danh sách địa chỉ giao hàng, danh sách món yêu thích và mã giảm giá mà khách hàng đang sở hữu.
>
> \+ Tìm kiếm: Tra cứu đơn hàng của khách theo trạng thái hoặc theo thời điểm đặt.
>
> \+ Thống kê, kết xuất: Không.
>
> \+ Tính toán, xử lý: Kiểm tra tính hợp lệ của dữ liệu hồ sơ; xác định địa chỉ mặc định khi khách có nhiều địa chỉ.

**- Đánh giá đơn hàng**

> \+ Lưu trữ: Số sao cho đơn hàng, số sao cho khâu giao hàng, nội dung nhận xét và thời điểm gửi, gắn với đúng một đơn hàng.
>
> \+ Tìm kiếm: Kiểm tra đơn hàng đã được đánh giá hay chưa.
>
> \+ Thống kê, kết xuất: Cung cấp dữ liệu cho báo cáo đánh giá của chi nhánh.
>
> \+ Tính toán, xử lý: Ràng buộc mỗi đơn chỉ nhận một đánh giá và chỉ nhận khi đơn đã hoàn thành.

**- Tư vấn thực đơn bằng trợ lý AI**

> \+ Lưu trữ: Phiên hội thoại của từng khách hàng và bộ từ khoá trả lời nhanh do quản trị viên soạn.
>
> \+ Tìm kiếm: Đối chiếu câu hỏi với bộ từ khoá; lấy danh sách món, giá đang bán tại chi nhánh khách đã chọn.
>
> \+ Thống kê, kết xuất: Không.
>
> \+ Tính toán, xử lý: Trả lời theo từ khoá nếu khớp, ngược lại gửi câu hỏi kèm thực đơn tới Google Gemini; giới hạn tối đa 10 tin nhắn mỗi phút cho mỗi khách.

**- Xử lý đơn hàng chi nhánh**

> \+ Lưu trữ: Trạng thái hiện tại của đơn và nhật ký chuyển trạng thái gồm người thực hiện, trạng thái trước, trạng thái sau, thời điểm và ghi chú.
>
> \+ Tìm kiếm: Tra cứu đơn theo mã đơn, theo trạng thái, theo hình thức nhận hàng hoặc theo khoảng thời gian.
>
> \+ Thống kê, kết xuất: Đếm số đơn đang tồn ở mỗi trạng thái và tính doanh thu hiển thị trên màn hình tổng quan.
>
> \+ Tính toán, xử lý: Kiểm soát trình tự chuyển trạng thái hợp lệ và chỉ cho phép huỷ đơn khi đơn ở trạng thái chờ xác nhận hoặc đã xác nhận.

**- Quản lý thực đơn và giá bán chi nhánh**

> \+ Lưu trữ: Danh sách món được bày bán tại chi nhánh kèm giá bán riêng, số lượng tồn, cờ còn hoặc hết món, nhãn Bán chạy và cờ món đề xuất của đầu bếp.
>
> \+ Tìm kiếm: Tìm món theo tên hoặc danh mục trong phạm vi chi nhánh.
>
> \+ Thống kê, kết xuất: Không.
>
> \+ Tính toán, xử lý: Kiểm tra giá bán phải là số dương; áp dụng ngay thay đổi lên thực đơn hiển thị cho khách hàng.

**- Quản lý nhân sự và ca làm việc**

> \+ Lưu trữ: Tài khoản nhân sự gắn với chi nhánh, vai trò, trạng thái hoạt động, đơn giá lương theo giờ, đơn giá thử việc, ngày bắt đầu làm việc; danh mục ca làm gồm tên ca, giờ bắt đầu và giờ kết thúc.
>
> \+ Tìm kiếm: Tra cứu nhân sự theo tên hoặc theo vai trò; tra cứu ca làm đang áp dụng.
>
> \+ Thống kê, kết xuất: Đếm số lượt chấm công đã phát sinh trên từng ca.
>
> \+ Tính toán, xử lý: Kiểm tra trùng thông tin tài khoản; kiểm tra giờ kết thúc ca phải sau giờ bắt đầu; giới hạn vai trò được cấp chỉ gồm nhân viên và đầu bếp.

**- Chấm công bằng nhận diện khuôn mặt**

> \+ Lưu trữ: Bộ mẫu đặc trưng khuôn mặt của nhân sự; thiết bị quầy đã ghép với chi nhánh; từng lượt chấm công kèm ca làm, giờ vào, giờ ra, hình thức chấm, độ tương đồng, lý do đi trễ hoặc ra sớm và ảnh minh chứng.
>
> \+ Tìm kiếm: Tra cứu bảng chấm công theo ngày hoặc theo nhân sự; đối chiếu đặc trưng khuôn mặt vừa thu nhận với các mẫu đã đăng ký.
>
> \+ Thống kê, kết xuất: Kết xuất bảng chấm công theo ngày ra tập tin Excel.
>
> \+ Tính toán, xử lý: So khớp khuôn mặt nhìn thẳng với bộ mẫu đã đăng ký theo ngưỡng khoảng cách; xác định ca làm và tính số giờ làm của mỗi lượt; xác định mức đi trễ, ra sớm so với giờ ca và bắt buộc nhập lý do khi vượt ngưỡng; tự xoá ảnh minh chứng sau ba mươi ngày.

**- Quản lý bảng lương**

> \+ Lưu trữ: Bảng lương theo kỳ của từng nhân sự gồm tổng giờ làm, số ngày công, lương cơ bản, tổng thưởng, tổng phạt, thực nhận và trạng thái; danh sách từng khoản thưởng, phạt kèm số tiền, lý do và người ghi.
>
> \+ Tìm kiếm: Tra cứu bảng lương theo kỳ hoặc theo nhân sự.
>
> \+ Thống kê, kết xuất: Tổng hợp chi phí lương của chi nhánh theo kỳ và kết xuất bảng lương ra Excel.
>
> \+ Tính toán, xử lý: Cộng dồn giờ làm từ dữ liệu chấm công, nhân với đơn giá theo giờ hoặc đơn giá thử việc; cộng lại tổng thưởng, tổng phạt từ các khoản và không cho phép thực nhận âm; bảng lương chỉ chuyển trạng thái theo một chiều.

**- Báo cáo doanh thu**

> \+ Lưu trữ: Không phát sinh dữ liệu mới, báo cáo được tổng hợp từ dữ liệu đơn hàng.
>
> \+ Tìm kiếm: Lọc dữ liệu theo ngày, tháng hoặc khoảng thời gian tuỳ chọn.
>
> \+ Thống kê, kết xuất: Hiển thị doanh thu, số đơn, tỉ lệ đơn theo trạng thái, cơ cấu theo phương thức thanh toán và danh sách món bán chạy dưới dạng bảng và biểu đồ; hỗ trợ kết xuất ra Excel.
>
> \+ Tính toán, xử lý: Cộng tổng doanh thu, đếm số đơn theo từng trạng thái và xếp hạng món theo số lượng bán.

**- Quản lý đánh giá của khách hàng**

> \+ Lưu trữ: Nội dung phản hồi của quản lý cùng thời điểm phản hồi, gắn với đánh giá tương ứng.
>
> \+ Tìm kiếm: Tra cứu đánh giá theo số sao, theo thời gian hoặc theo mã đơn hàng.
>
> \+ Thống kê, kết xuất: Tính điểm đánh giá trung bình của chi nhánh và số lượng đánh giá theo từng mức sao.
>
> \+ Tính toán, xử lý: Không cho phép lưu phản hồi rỗng; chỉ quản lý của chi nhánh liên quan mới được phản hồi.

**- Quản trị hệ thống và cấp phát tài khoản quản lý**

> \+ Lưu trữ: Chi nhánh, tài khoản quản lý, danh mục và món ăn gốc, banner, mã giảm giá, cài đặt chung, từ khoá chatbot, tin nhắn liên hệ và nhật ký thao tác của quản trị viên.
>
> \+ Tìm kiếm: Tra cứu chi nhánh, tài khoản, đơn hàng, khách hàng toàn chuỗi; kiểm tra trùng thông tin tài khoản trước khi tạo.
>
> \+ Thống kê, kết xuất: Tổng quan doanh thu, số đơn của toàn chuỗi và từng chi nhánh; báo cáo kết xuất ra Excel.
>
> \+ Tính toán, xử lý: Mã hoá mật khẩu khởi tạo, gán vai trò quản lý cho đúng một chi nhánh; ghi nhật ký mỗi thao tác thay đổi dữ liệu.

**d. Các yêu cầu phi chức năng**

**Bảo mật và phân quyền.** Mật khẩu được mã hoá một chiều trước khi lưu. Mọi đường dẫn thuộc khu vực quản trị đều đi qua lớp kiểm tra vai trò, đồng thời dữ liệu truy xuất luôn bị giới hạn theo chi nhánh của người đăng nhập để ngăn tình trạng một quản lý xem được số liệu của chi nhánh khác. Dữ liệu đặc trưng khuôn mặt chỉ được dùng cho mục đích xác thực chấm công; ảnh minh chứng được lưu trong ba mươi ngày rồi xoá tự động.

**Tính đúng đắn của dữ liệu nghiệp vụ.** Các nghiệp vụ liên quan đến tiền và giờ công phải cho kết quả nhất quán: số giờ làm tính từ dữ liệu chấm công, tiền lương tính từ số giờ làm và đơn giá, doanh thu tính từ các đơn đã hoàn thành. Trạng thái đơn hàng và trạng thái bảng lương chỉ được chuyển theo đúng trình tự đã quy định.

**Hiệu năng và tính sẵn dùng.** Thao tác nhận diện khuôn mặt tại quầy và thao tác duyệt đơn cần phản hồi trong thời gian ngắn để không gây ùn tắc trong giờ cao điểm. Màn hình tổng quan được thiết kế gọn, hiển thị đủ số liệu cần thiết trong một lần tải trang.

**Khả năng tương thích.** Giao diện hiển thị tốt trên điện thoại của khách hàng cũng như trên máy tính và máy tính bảng tại quầy. Thiết bị dùng để chấm công cần có webcam và trình duyệt hỗ trợ truy cập camera.

**Khả năng mở rộng.** Cấu trúc dữ liệu gắn với thực thể chi nhánh cho phép bổ sung điểm bán mới, thêm ca làm hoặc thêm thiết bị chấm công mà không phải thay đổi thiết kế chung.

**Môi trường triển khai.** Hệ thống hoạt động theo mô hình khách – chủ. Phía người dùng chỉ cần trình duyệt thông dụng và kết nối Internet ổn định. Phía máy chủ cần môi trường chạy PHP 8.3 cùng hệ quản trị cơ sở dữ liệu MySQL; trong giai đoạn thực hiện đồ án, toàn bộ hệ thống được triển khai trên máy chủ cục bộ dựng bằng Laragon.

### 2.1.7. Sơ đồ Use Case

**a. Danh sách các Use Case**

*Bảng* 5*: Danh sách các use case.*

| **Mã** | **Tên Use Case** | **Ý nghĩa** |
|:--:|----|----|
| UC01 | Đăng ký tài khoản | Khách hàng tạo tài khoản mới để sử dụng các chức năng cần định danh như đặt hàng, theo dõi đơn và đánh giá. |
| UC02 | Đăng nhập và đăng xuất | Các tác nhân xác thực danh tính để vào đúng khu vực làm việc theo vai trò và kết thúc phiên làm việc an toàn. |
| UC03 | Tra cứu thực đơn và đặt hàng | Khách hàng chọn chi nhánh, tìm và xem chi tiết món, quản lý giỏ hàng rồi tạo đơn theo hình thức mang đi hoặc giao tận nơi. |
| UC04 | Thanh toán đơn hàng | Khách hàng chọn phương thức thanh toán cho đơn vừa tạo và nhận thông tin xác nhận đơn hàng. |
| UC05 | Quản lý hồ sơ và theo dõi đơn hàng | Khách hàng cập nhật hồ sơ, địa chỉ giao hàng, món yêu thích, kho mã giảm giá và theo dõi tiến trình các đơn đã đặt. |
| UC06 | Đánh giá đơn hàng | Khách hàng chấm sao và nhận xét về món ăn cùng khâu giao hàng sau khi đơn hoàn thành. |
| UC07 | Tư vấn thực đơn bằng trợ lý AI | Khách hàng đã đăng nhập hỏi về món ăn, giá hoặc cách đặt món; trợ lý trả lời theo từ khoá hoặc qua Google Gemini dựa trên thực đơn của chi nhánh. |
| UC08 | Xử lý đơn hàng chi nhánh | Quản lý duyệt, huỷ và cập nhật tiến trình đơn hàng của chi nhánh, kèm nhật ký cho từng lần chuyển trạng thái. |
| UC09 | Quản lý thực đơn và giá bán chi nhánh | Quản lý chọn món bày bán, đặt giá riêng, cập nhật tồn và đánh dấu còn hoặc hết món cho chi nhánh. |
| UC10 | Quản lý nhân sự và ca làm việc | Quản lý cấp và kiểm soát tài khoản nhân viên, đầu bếp; khai báo, điều chỉnh danh mục ca làm và xếp lịch làm theo tuần cho chi nhánh. |
| UC11 | Chấm công bằng nhận diện khuôn mặt | Quản lý đăng ký mẫu khuôn mặt, ghép và thu hồi thiết bị quầy; nhân viên, đầu bếp chấm vào, chấm ra, ra ca sớm; quản lý chấm công thủ công khi cần. |
| UC12 | Quản lý bảng lương | Quản lý tính lương theo giờ công, ghi từng khoản thưởng và phạt kèm lý do, chốt bảng lương theo ba bước và kết xuất ra Excel. |
| UC13 | Báo cáo doanh thu | Quản lý chi nhánh xem số lượng đơn hàng, doanh thu theo khoảng thời gian, theo phương thức thanh toán và thống kê nhóm món bán chạy của chi nhánh. |
| UC14 | Theo dõi báo cáo và đánh giá chi nhánh | Quản lý xem báo cáo doanh thu theo thời gian, phương thức thanh toán, món bán chạy và phản hồi các đánh giá của khách hàng. |
| UC15 | Quản trị hệ thống và cấp phát tài khoản quản lý | Quản trị viên quản lý dữ liệu nền của chuỗi và cấp tài khoản quản lý cho từng chi nhánh trên trang quản trị. |

**b. Danh sách các Actor**

*Bảng* 6*: Danh sách các actor.*

| **Mã** | **Tác nhân** | **Mã Use Case** |
|:--:|----|----|
| TC01 | Khách hàng | UC01, UC02, UC03, UC04, UC05, UC06, UC07 |
| TC02 | Quản lý chi nhánh | UC02, UC08, UC09, UC10, UC11, UC12, UC13, UC14 |
| TC03 | Nhân viên | UC02, UC11 |
| TC04 | Đầu bếp | UC02, UC11 |
| TC05 | Quản trị viên hệ thống | UC02, UC15 |

**c. Biểu đồ Use Case tổng quát**


*Hình* 1*: Sơ đồ use case tổng quát.*

**d. Sơ đồ Use Case chi tiết**

**- UC01 – Đăng ký**


*Hình* 2*: Sơ đồ use case đăng ký.*

**- UC02 – Đăng nhập và đăng xuất**


*Hình* 3*: Sơ đồ use case đăng nhập và đăng xuất.*

**- UC03 – Tra cứu thực đơn và đặt hàng**


*Hình* 4*: Sơ đồ use case tra cứu thực đơn và đặt hàng.*

**- UC04 – Thanh toán đơn hàng**


*Hình* 5*: Sơ đồ use case thanh toán đơn hàng.*

**- UC05 – Quản lý hồ sơ và theo dõi đơn hàng**


*Hình* 6*: Sơ đồ use case quản lý hồ sơ và theo dõi đơn hàng.*

**- UC06 – Đánh giá đơn hàng**


*Hình* 7*: Sơ đồ use case đánh giá đơn hàng.*

**- UC07 – Tư vấn thực đơn bằng trợ lý AI**


*Hình* 8*: Sơ đồ use case tư vấn thực đơn bằng trợ lý AI.*

**- UC08 – Xử lý đơn hàng chi nhánh**


*Hình* 9*: Sơ đồ use case xử lý đơn hàng chi nhánh.*

**- UC09 – Quản lý thực đơn và giá bán chi nhánh**


*Hình* 10*: Sơ đồ use case quản lý thực đơn và giá bán chi nhánh.*

**- UC10 – Quản lý nhân sự và ca làm việc**


*Hình* 11*: Sơ đồ use case quản lý nhân sự và ca làm việc.*

**- UC11 – Chấm công bằng nhận diện khuôn mặt**


*Hình* 12*: Sơ đồ use case chấm công bằng nhận diện khuôn mặt.*

**- UC12 – Quản lý bảng lương**


*Hình* 13*: Sơ đồ use case quản lý bảng lương.*

**- UC13 – Báo cáo doanh thu**


*Hình* 14*: Sơ đồ use case báo cáo doanh thu.*

**- UC14 – Theo dõi báo cáo và đánh giá chi nhánh**


*Hình* 15*: Sơ đồ use case theo dõi báo cáo và đánh giá chi nhánh.*

**- UC15 – Quản trị hệ thống và cấp tài khoản quản lý**


*Hình* 16*: Sơ đồ use case quản trị hệ thống và cấp tài khoản quản lý.*

**e. Đặc tả Use Case**

**- UC01 – Đăng ký**

*Bảng* 7*: Đặc tả UC01 – Đăng ký.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: center;"><p>Đăng ký</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Khách hàng</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Người dùng chưa có tài khoản và đang truy cập trang web ZomZop.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Khách hàng chọn chức năng đăng ký.</p>
<p>2. Hệ thống hiển thị biểu mẫu gồm họ tên, số điện thoại, hộp thư điện tử và mật khẩu.</p>
<p>3. Khách hàng nhập thông tin rồi xác nhận.</p>
<p>4. Hệ thống kiểm tra định dạng dữ liệu và đối chiếu số điện thoại, hộp thư với các tài khoản đã có.</p>
<p>5. Hệ thống mã hoá mật khẩu, tạo tài khoản với vai trò khách hàng.</p>
<p>6. Hệ thống thông báo đăng ký thành công và chuyển sang trang đăng nhập.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>4a. Trường bắt buộc còn trống hoặc sai định dạng: hệ thống đánh dấu lỗi tại từng trường và giữ lại dữ liệu đã nhập.</p>
<p>4b. Số điện thoại hoặc hộp thư đã được sử dụng: hệ thống từ chối tạo tài khoản và hướng người dùng sang chức năng đăng nhập.</p></td>
</tr>
</tbody>
</table>

**- UC02 – Đăng nhập và đăng xuất**

*Bảng* 8*: Đặc tả UC02 – Đăng nhập và đăng xuất.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: center;"><p>Đăng nhập và đăng xuất</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: left;"><p>Khách hàng, Nhân viên, Đầu bếp, Quản lý chi nhánh, Quản trị viên hệ thống</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Tài khoản đã tồn tại trong hệ thống và không ở trạng thái bị khoá.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Người dùng mở trang đăng nhập và nhập hộp thư điện tử cùng mật khẩu; khách hàng có thể chọn đăng nhập bằng tài khoản Google hoặc Facebook.</p>
<p>2. Hệ thống đối chiếu thông tin với dữ liệu tài khoản.</p>
<p>3. Hệ thống đọc vai trò của tài khoản vừa xác thực.</p>
<p>4. Hệ thống điều hướng người dùng: khách hàng vào trang bán hàng, quản lý vào khu vực quản trị chi nhánh, nhân viên và đầu bếp được đưa về trang chủ vì nghiệp vụ của họ thực hiện tại thiết bị chấm công.</p>
<p>5. Khi kết thúc công việc, người dùng chọn đăng xuất và hệ thống huỷ phiên làm việc.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>2a. Thông tin đăng nhập không đúng: hệ thống thông báo lỗi chung mà không chỉ rõ sai ở mật khẩu hay tài khoản.</p>
<p>3a. Tài khoản đang bị khoá: hệ thống từ chối truy cập và đề nghị người dùng liên hệ quản lý chi nhánh.</p></td>
</tr>
</tbody>
</table>

**- UC03 – Tra cứu thực đơn và đặt hàng**

*Bảng* 9*: Đặc tả UC03 – Tra cứu thực đơn và đặt hàng.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: center;"><p>Tra cứu thực đơn và đặt hàng</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Khách hàng</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Hệ thống có ít nhất một chi nhánh đang hoạt động. Khách hàng xem thực đơn và thêm món vào giỏ mà không cần đăng nhập, nhưng phải đăng nhập trước khi xác nhận đặt hàng.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Khách hàng chọn chi nhánh muốn mua hàng.</p>
<p>2. Hệ thống tải thực đơn của chi nhánh kèm giá bán và tình trạng còn món.</p>
<p>3. Khách hàng tìm món theo tên hoặc lọc theo danh mục, xem chi tiết món khi cần.</p>
<p>4. Khách hàng thêm món vào giỏ, ghi chú và điều chỉnh số lượng. 5. Khách hàng chọn hình thức nhận hàng là mang đi hoặc giao tận nơi.</p>
<p>6. Trường hợp giao tận nơi, khách hàng chọn địa chỉ trong sổ địa chỉ và tốc độ giao tiêu chuẩn hoặc hỏa tốc.</p>
<p>7. Khách hàng nhập hoặc chọn mã giảm giá từ danh sách gợi ý. 8. Hệ thống hiển thị tiền món, phí giao, số tiền giảm và tổng tiền. 9. Khách hàng xác nhận tạo đơn.</p>
<p>10. Hệ thống kiểm tra lại giá và mã, sinh mã đơn, lưu đơn ở trạng thái chờ xác nhận và hiển thị đơn mới trên màn hình tổng quan của chi nhánh.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>2a. Chi nhánh đang tạm đóng: hệ thống đề nghị khách chọn chi nhánh khác.</p>
<p>4a. Món đã hết hoặc ngừng bán: hệ thống không cho thêm vào giỏ.</p>
<p>6a. Khách chưa có địa chỉ: hệ thống chuyển tới trang thêm địa chỉ rồi quay lại thanh toán.</p>
<p>7a. Mã không hợp lệ, hết hạn, chưa đủ giá trị đơn tối thiểu hoặc không đúng loại đơn: hệ thống nêu rõ lý do và không áp dụng.</p>
<p>9a. Giỏ hàng trống: hệ thống không cho phép xác nhận đơn.</p></td>
</tr>
</tbody>
</table>

**- UC04 – Thanh toán đơn hàng**

*Bảng* 10*: Đặc tả UC04 – Thanh toán đơn hàng.*

<table style="width:96%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: center;"><p>Thanh toán đơn hàng</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Khách hàng</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Khách hàng đã hoàn tất việc chọn món và hệ thống đã tính xong số tiền phải trả.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Hệ thống hiển thị trang thanh toán gồm tổng tiền và các phương thức đang được bật: tiền mặt, chuyển khoản VietQR, MoMo, VNPay.</p>
<p>2. Khách hàng chọn một phương thức.</p>
<p>3. Khách hàng xác nhận thanh toán.</p>
<p>4. Hệ thống lưu phương thức đã chọn kèm đơn hàng và đặt trạng thái thanh toán là chờ thanh toán.</p>
<p>5. Hệ thống hiển thị thông báo đặt hàng thành công cùng mã đơn để khách hàng tra cứu.</p>
<p>6. Đối với đơn tiền mặt, hệ thống tự động cập nhật trạng thái đã thanh toán khi quản lý chuyển đơn sang hoàn thành.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>2a. Khách hàng chọn MoMo hoặc VNPay: trong phạm vi đồ án, hệ thống chỉ ghi nhận lựa chọn để phục vụ đối soát và chưa chuyển sang cổng thanh toán của nhà cung cấp.</p>
<p>2b. Khách hàng chọn chuyển khoản: hệ thống hiển thị mã VietQR kèm số tiền và nội dung chuyển khoản; đơn giữ trạng thái chưa thanh toán cho đến khi quản lý bấm xác nhận đã nhận tiền.</p>
<p>3a. Khách hàng thoát khỏi trang trước khi xác nhận: đơn chưa được tạo, giỏ hàng vẫn được giữ nguyên để khách tiếp tục sau.</p></td>
</tr>
</tbody>
</table>

**- UC05 – Quản lý hồ sơ và theo dõi đơn hàng**

*Bảng* 11*: Đặc tả UC05 – Quản lý hồ sơ và theo dõi đơn hàng.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: left;"><p>Quản lý hồ sơ và theo dõi đơn hàng</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Khách hàng</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: center;"><p>Khách hàng đã đăng nhập vào hệ thống.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Khách hàng mở mục tài khoản cá nhân.</p>
<p>2. Hệ thống hiển thị thông tin hồ sơ, sổ địa chỉ, món yêu thích, kho mã giảm giá và danh sách đơn hàng.</p>
<p>3. Khách hàng cập nhật hồ sơ hoặc thêm, sửa, xoá địa chỉ giao hàng.</p>
<p>4. Khách hàng lọc đơn hàng theo trạng thái và mở một đơn để xem chi tiết.</p>
<p>5. Hệ thống hiển thị danh sách món, tiền món, phí giao, giảm giá, tổng tiền, hình thức nhận hàng và lịch sử trạng thái của đơn.</p>
<p>6. Khách hàng có thể chọn đặt lại đơn; hệ thống xem trước giá hiện hành, bỏ qua món đã ngừng bán rồi thay giỏ hàng khi khách đồng ý.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>3a. Dữ liệu cập nhật sai định dạng: hệ thống báo lỗi tại trường tương ứng và không lưu thay đổi.</p>
<p>3b. Khách hàng xoá địa chỉ đang gắn với đơn chưa hoàn thành: hệ thống giữ lại thông tin địa chỉ trên đơn đã tạo để không làm sai lệch dữ liệu giao hàng.</p>
<p>4a. Khách hàng chưa phát sinh đơn nào: hệ thống hiển thị thông báo danh sách trống kèm liên kết dẫn tới thực đơn.</p></td>
</tr>
</tbody>
</table>

**- UC06 – Đánh giá đơn hàng**

*Bảng* 12*: Đặc tả UC06 – Đánh giá đơn hàng.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: center;"><p>Đánh giá đơn hàng</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Khách hàng</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Đơn hàng đã ở trạng thái hoàn thành và chưa được đánh giá.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Khách hàng mở chi tiết một đơn đã hoàn thành.</p>
<p>2. Khách hàng chọn chức năng đánh giá.</p>
<p>3. Khách hàng chấm số sao cho đơn hàng; với đơn giao tận nơi, khách hàng chấm thêm số sao cho khâu giao hàng.</p>
<p>4. Khách hàng nhập nhận xét nếu muốn và gửi đánh giá.</p>
<p>5. Hệ thống lưu đánh giá gắn với đơn, cập nhật điểm trung bình của chi nhánh và chuyển đánh giá vào mục theo dõi của quản lý.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>2a. Đơn chưa hoàn thành: hệ thống không hiển thị chức năng đánh giá.</p>
<p>2b. Đơn đã được đánh giá trước đó: hệ thống chỉ cho xem lại nội dung đã gửi.</p>
<p>3a. Khách hàng chưa chọn số sao: hệ thống yêu cầu chấm điểm trước khi gửi.</p></td>
</tr>
</tbody>
</table>

**- UC07 – Tư vấn thực đơn bằng trợ lý AI**

*Bảng* 13*: Đặc tả UC07 – Tư vấn thực đơn bằng trợ lý AI.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: center;"><p>Tư vấn thực đơn bằng trợ lý AI</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Khách hàng</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Khách hàng đã đăng nhập và đang truy cập trang bán hàng.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Khách hàng mở khung hội thoại của trợ lý.</p>
<p>2. Khách hàng nhập câu hỏi, chẳng hạn hỏi giờ mở cửa, mã giảm giá hoặc nhờ gợi ý món trong một mức giá.</p>
<p>3. Hệ thống đối chiếu câu hỏi với bộ từ khoá; nếu khớp, trả lời ngay kèm nút dẫn tới trang liên quan.</p>
<p>4. Nếu không khớp, hệ thống gửi câu hỏi cùng danh sách món và giá đang bán tại chi nhánh tới Google Gemini.</p>
<p>5. Trợ lý trả lời gợi ý khách quan kèm tên món, mức giá tham khảo.</p>
<p>6. Hệ thống lưu câu hỏi và câu trả lời vào phiên hội thoại của khách.</p>
<p>7. Khách hàng có thể hỏi tiếp hoặc chuyển sang thực đơn để đặt món.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>3a. Câu hỏi nằm ngoài phạm vi món ăn và cửa hàng: trợ lý từ chối nhẹ nhàng và gợi ý hỏi về món ăn.</p>
<p>4a. Không kết nối được dịch vụ Gemini: hệ thống thông báo trợ lý tạm thời chưa trả lời được.</p>
<p>4b. Khách gửi quá 10 tin nhắn trong một phút: hệ thống yêu cầu chờ rồi gửi lại.</p></td>
</tr>
</tbody>
</table>

**- UC08 – Xử lý đơn hàng chi nhánh**

*Bảng* 14*: Đặc tả UC08 – Xử lý đơn hàng chi nhánh.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: center;"><p>Xử lý đơn hàng chi nhánh</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Quản lý chi nhánh</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Quản lý đã đăng nhập vào khu vực quản trị và chi nhánh có đơn hàng phát sinh.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Quản lý mở màn hình tổng quan hoặc danh sách đơn hàng và lọc theo trạng thái.</p>
<p>2. Quản lý xem chi tiết đơn gồm danh sách món, hình thức nhận hàng, thông tin người nhận và số tiền.</p>
<p>3. Quản lý duyệt đơn, đơn chuyển từ chờ xác nhận sang đã xác nhận.</p>
<p>4. Khi bếp bắt đầu chế biến, quản lý chuyển đơn sang đang nấu. 5. Khi món đã xong, quản lý chuyển đơn sang sẵn sàng.</p>
<p>6. Sau khi khách nhận hàng, quản lý xác nhận đơn hoàn thành; đơn tiền mặt đồng thời được ghi nhận đã thanh toán.</p>
<p>7. Hệ thống lưu một dòng nhật ký cho mỗi lần chuyển trạng thái, gồm người thực hiện, trạng thái trước, trạng thái sau, thời điểm và ghi chú.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>3a. Quản lý huỷ đơn: hệ thống chỉ cho phép khi đơn ở trạng thái chờ xác nhận hoặc đã xác nhận, đồng thời yêu cầu nhập lý do huỷ. 4a. Quản lý chuyển trạng thái không theo trình tự, ví dụ từ chờ xác nhận sang sẵn sàng: hệ thống từ chối và giữ nguyên trạng thái hiện tại.</p>
<p>6a. Khách không nhận hàng: quản lý ghi chú vào đơn để làm căn cứ xử lý, đơn không được chuyển sang hoàn thành.</p></td>
</tr>
</tbody>
</table>

**- UC09 – Quản lý thực đơn và giá bán chi nhánh**

*Bảng* 15*: Đặc tả UC09 – Quản lý thực đơn và giá bán chi nhánh.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: left;"><p>Quản lý thực đơn và giá bán chi nhánh</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Quản lý chi nhánh</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Quản lý đã đăng nhập và danh mục món của hệ thống đã được khởi tạo.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Quản lý mở mục thực đơn và giá bán của chi nhánh.</p>
<p>2. Hệ thống hiển thị danh sách món kèm giá đang áp dụng, số lượng tồn và tình trạng còn hoặc hết món.</p>
<p>3. Quản lý chọn món cần điều chỉnh.</p>
<p>4. Quản lý thay đổi giá bán riêng cho chi nhánh, cập nhật số lượng tồn hoặc đánh dấu hết món.</p>
<p>5. Quản lý lưu thay đổi.</p>
<p>6. Hệ thống kiểm tra dữ liệu, ghi nhận cập nhật và áp dụng ngay lên thực đơn hiển thị cho khách hàng.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>4a. Giá bán nhập vào không phải số dương: hệ thống báo lỗi và không lưu.</p>
<p>4b. Quản lý tắt một món đang nằm trong đơn chưa hoàn thành: hệ thống vẫn giữ món trên đơn cũ, chỉ ngừng hiển thị món với các đơn mới.</p>
<p>6a. Mất kết nối trong lúc lưu: hệ thống thông báo cập nhật không thành công và giữ nguyên giá trị trước đó.</p></td>
</tr>
</tbody>
</table>

**- UC10 – Quản lý nhân sự và ca làm việc**

*Bảng* 16*: Đặc tả UC10 – Quản lý nhân sự và ca làm việc.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: center;"><p>Quản lý nhân sự và ca làm việc</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Quản lý chi nhánh</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Quản lý đã đăng nhập vào khu vực quản trị chi nhánh.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Quản lý mở mục nhân viên, hệ thống hiển thị danh sách nhân sự của chi nhánh kèm vai trò và trạng thái tài khoản.</p>
<p>2. Quản lý tạo tài khoản mới, nhập họ tên, số điện thoại, mật khẩu khởi tạo, đơn giá lương theo giờ và chọn vai trò nhân viên hoặc đầu bếp.</p>
<p>3. Hệ thống kiểm tra dữ liệu, tạo tài khoản và gán vào chi nhánh của quản lý.</p>
<p>4. Khi cần, quản lý khoá hoặc mở lại tài khoản, đặt lại mật khẩu hoặc điều chỉnh đơn giá lương.</p>
<p>5. Quản lý mở mục ca làm, hệ thống hiển thị danh mục ca kèm giờ bắt đầu, giờ kết thúc và số lượt chấm công đã phát sinh.</p>
<p>6. Quản lý sửa giờ của ca đang dùng hoặc thêm ca mới rồi lưu lại.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>2a. Số điện thoại đã gắn với tài khoản khác: hệ thống từ chối tạo và thông báo trùng.</p>
<p>2b. Quản lý cố cấp vai trò quản lý hoặc quản trị viên: hệ thống không cho phép, danh sách vai trò chỉ gồm nhân viên và đầu bếp. 6a. Giờ kết thúc ca không sau giờ bắt đầu: hệ thống báo lỗi và không lưu.</p>
<p>6b. Quản lý sửa giờ của ca đã có lượt chấm công: hệ thống chỉ áp dụng giờ mới cho các lượt chấm công phát sinh sau đó.</p></td>
</tr>
</tbody>
</table>

**- UC11 – Chấm công bằng nhận diện khuôn mặt**

*Bảng* 17*: Đặc tả UC11 – Chấm công bằng nhận diện khuôn mặt.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: center;"><p>Chấm công bằng nhận diện khuôn mặt</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Nhân viên, Đầu bếp</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân phụ</strong></p></td>
<td style="text-align: center;"><p>Quản lý chi nhánh</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Quản lý đã đăng ký khuôn mặt cho nhân sự và đã ghép thiết bị tại quầy với chi nhánh; thiết bị có webcam và được cấp quyền truy cập camera.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Quản lý tạo liên kết ghép thiết bị, mở liên kết trên máy đặt tại quầy để thiết bị trở thành máy chấm công của chi nhánh.</p>
<p>2. Quản lý đăng ký khuôn mặt cho nhân sự: trang tự chụp 15 mẫu nhìn thẳng hoặc quản lý tải lên 3–15 ảnh; hệ thống kiểm tra các mẫu thuộc cùng một người rồi lưu đặc trưng.</p>
<p>3. Nhân sự đến thiết bị và bấm một trong các nút chấm vào, chấm ra hoặc ra ca sớm.</p>
<p>4. Thiết bị bật camera, nhân sự nhìn thẳng; trình duyệt trích xuất đặc trưng và so khớp với mẫu đã đăng ký của chi nhánh.</p>
<p>5. Hệ thống xác định ca làm, ghi nhận thời điểm, hình thức chấm, độ tương đồng và ảnh minh chứng.</p>
<p>6. Nếu chấm vào trễ hơn giờ ca quá năm phút hoặc chấm ra sớm hơn giờ kết thúc quá mười phút, hệ thống yêu cầu chọn lý do rồi mới hoàn tất.</p>
<p>7. Thiết bị thông báo kết quả kèm họ tên nhân sự và thời điểm vừa ghi nhận.</p>
<p>8. Quản lý tra cứu bảng chấm công theo ngày và kết xuất ra Excel khi cần.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>4a. Không phát hiện được khuôn mặt hoặc khuôn mặt không nhìn thẳng: hệ thống yêu cầu thực hiện lại.</p>
<p>4b. Không tìm được mẫu khuôn mặt trùng khớp: hệ thống từ chối chấm công và đề nghị liên hệ quản lý.</p>
<p>5a. Nhân sự chấm ra khi chưa có bản ghi chấm vào: hệ thống thông báo và không tạo bản ghi.</p>
<p>8a. Thiết bị hỏng hoặc nhân sự quên chấm: quản lý dùng chức năng chấm công thủ công, chọn nhân viên, ca làm, nhập giờ vào, giờ ra và ghi chú lý do.</p>
<p>8b. Thiết bị không còn sử dụng hoặc bị thất lạc: quản lý thu hồi thiết bị, mọi lượt chấm công từ thiết bị đó bị từ chối.</p></td>
</tr>
</tbody>
</table>

**- UC12 – Quản lý bảng lương**

*Bảng* 18*: Đặc tả UC12 – Quản lý bảng lương.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: center;"><p>Quản lý bảng lương</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Quản lý chi nhánh</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Dữ liệu chấm công của kỳ lương đã được ghi nhận; nhân sự đã được thiết lập đơn giá lương theo giờ.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Quản lý mở mục bảng lương và chọn tháng cần xem.</p>
<p>2. Hệ thống cộng dồn số giờ làm thực tế của từng nhân sự từ dữ liệu chấm công, nhân với đơn giá theo giờ và tự áp dụng đơn giá thử việc cho những ngày trong thời gian thử việc.</p>
<p>3. Hệ thống hiển thị bảng lương ở trạng thái nháp.</p>
<p>4. Quản lý mở phần thưởng/phạt của từng nhân sự, thêm từng khoản với loại, số tiền và lý do; có thể sửa hoặc xoá khoản đã ghi. 5. Hệ thống cộng lại tổng thưởng, tổng phạt và thực nhận.</p>
<p>6. Quản lý chốt bảng lương, sau khi chi trả thì đánh dấu đã trả.</p>
<p>7. Quản lý kết xuất bảng lương theo tháng hoặc năm ra tập tin Excel.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>2a. Nhân sự có bản ghi chấm vào nhưng thiếu chấm ra: lượt đó chưa được tính giờ, quản lý bổ sung thủ công rồi bấm tính lại.</p>
<p>4a. Khoản thưởng, phạt thiếu lý do hoặc số tiền không hợp lệ: hệ thống báo lỗi và không lưu.</p>
<p>4b. Tổng phạt làm thực nhận âm: hệ thống từ chối khoản vừa nhập.</p>
<p>6a. Bảng lương đã chốt hoặc đã trả: hệ thống không cho sửa các khoản và không cho chuyển về trạng thái trước đó.</p></td>
</tr>
</tbody>
</table>

**- UC13 – Báo cáo doanh thu**

*Bảng* 19*: Đặc tả UC13 – Báo cáo doanh thu.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><strong>Mô tả</strong></th>
<th style="text-align: center;"><strong>Ý nghĩa</strong></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><strong>Tên Use Case</strong></td>
<td style="text-align: center;">Báo cáo doanh thu</td>
</tr>
<tr>
<td style="text-align: center;"><strong>Tác nhân chính</strong></td>
<td style="text-align: center;">Quản lý chi nhánh</td>
</tr>
<tr>
<td style="text-align: center;"><strong>Tiền điều kiện</strong></td>
<td style="text-align: left;">Quản lý đã đăng nhập thành công, có quyền truy cập chức năng báo cáo và được phân công quản lý chi nhánh.</td>
</tr>
<tr>
<td style="text-align: center;"><strong>Luồng sự kiện chính</strong></td>
<td style="text-align: left;">1. Quản lý truy cập chức năng <strong>Báo cáo doanh thu</strong>.<br />
2. Hệ thống hiển thị báo cáo của chi nhánh đang quản lý theo khoảng thời gian mặc định.<br />
3. Quản lý chọn khoảng thời gian cần thống kê và yêu cầu xem báo cáo.<br />
4. Hệ thống kiểm tra tính hợp lệ của khoảng thời gian, truy xuất và tổng hợp dữ liệu đơn hàng thuộc chi nhánh.<br />
5. Hệ thống hiển thị số lượng đơn hàng, doanh thu trong khoảng thời gian đã chọn, doanh thu theo phương thức thanh toán và thống kê các món bán chạy.<br />
6. Quản lý xem kết quả, thay đổi khoảng thời gian để tiếp tục tra cứu hoặc kết thúc chức năng.</td>
</tr>
<tr>
<td style="text-align: center;"><strong>Luồng ngoại lệ</strong></td>
<td style="text-align: left;">1. Khoảng thời gian không hợp lệ: Nếu ngày bắt đầu lớn hơn ngày kết thúc hoặc dữ liệu ngày không đúng định dạng, hệ thống thông báo và yêu cầu chọn lại.<br />
2. Không có dữ liệu: Nếu không có đơn hàng phù hợp trong khoảng thời gian đã chọn, hệ thống hiển thị thông báo không có dữ liệu; số lượng đơn và doanh thu bằng 0.<br />
3. Không có quyền truy cập hoặc phiên đăng nhập hết hạn, Hệ thống từ chối truy cập hoặc yêu cầu đăng nhập lại.<br />
4. Lỗi truy xuất dữ liệu, Hệ thống thông báo không thể tải báo cáo và cho phép thử lại.</td>
</tr>
</tbody>
</table>

**- UC14 – Theo dõi báo cáo và đánh giá chi nhánh**

*Bảng* 20*: Đặc tả UC14 – Theo dõi báo cáo và đánh giá chi nhánh.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: left;"><p>Theo dõi báo cáo và đánh giá chi nhánh</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Quản lý chi nhánh</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Quản lý đã đăng nhập; chi nhánh đã phát sinh đơn hàng hoặc đánh giá.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Quản lý mở mục báo cáo doanh thu và chọn khoảng thời gian cần xem.</p>
<p>2. Hệ thống tổng hợp số đơn, doanh thu, cơ cấu theo phương thức thanh toán, tỉ lệ đơn theo trạng thái và danh sách món bán chạy. 3. Quản lý xem số liệu dưới dạng bảng và biểu đồ, kết xuất ra Excel khi cần.</p>
<p>4. Quản lý mở mục đánh giá, hệ thống hiển thị danh sách đánh giá của chi nhánh kèm số sao và nhận xét.</p>
<p>5. Quản lý chọn một đánh giá, nhập nội dung phản hồi và gửi.</p>
<p>6. Hệ thống lưu phản hồi, hiển thị tới khách hàng đã gửi đánh giá.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>2a. Khoảng thời gian được chọn chưa có dữ liệu: hệ thống hiển thị báo cáo với giá trị bằng không kèm thông báo chưa phát sinh số liệu.</p>
<p>2b. Ngày kết thúc nhỏ hơn ngày bắt đầu: hệ thống báo lỗi và yêu cầu chọn lại.</p>
<p>5a. Nội dung phản hồi để trống: hệ thống không cho phép gửi.</p></td>
</tr>
</tbody>
</table>

**- UC15 – Quản trị hệ thống và cấp phát tài khoản quản lý**

*Bảng* 21*: Đặc tả UC15 – Quản trị tài khoản hệ thống và cấp phát tài khoản quản lý.*

<table style="width:97%;">
<colgroup>
<col style="width: 24%" />
<col style="width: 72%" />
</colgroup>
<thead>
<tr>
<th style="text-align: center;"><p><strong>Mô tả</strong></p></th>
<th style="text-align: center;"><p><strong>Ý nghĩa</strong></p></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center;"><p><strong>Tên Use Case</strong></p></td>
<td style="text-align: left;"><p>Quản trị hệ thống và cấp phát tài khoản quản lý</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tác nhân chính</strong></p></td>
<td style="text-align: center;"><p>Quản trị viên hệ thống</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Tiền điều kiện</strong></p></td>
<td style="text-align: left;"><p>Quản trị viên đã đăng nhập vào khu vực quản trị.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng sự kiện chính</strong></p></td>
<td style="text-align: left;"><p>1. Quản trị viên khởi tạo dữ liệu nền của chuỗi gồm chi nhánh, danh mục, món ăn, banner, mã giảm giá và cài đặt chung.</p>
<p>2. Quản trị viên mở mục tài khoản quản lý và chọn tạo mới.</p>
<p>3. Hệ thống yêu cầu nhập họ tên, hộp thư, số điện thoại, mật khẩu khởi tạo và chi nhánh được phân công.</p>
<p>4. Hệ thống kiểm tra dữ liệu, mã hoá mật khẩu, tạo tài khoản với vai trò quản lý và ghi nhật ký thao tác.</p>
<p>5. Quản trị viên chuyển thông tin đăng nhập cho người phụ trách chi nhánh.</p>
<p>6. Trong quá trình vận hành, quản trị viên soạn từ khoá chatbot, xử lý tin nhắn liên hệ và theo dõi báo cáo toàn chuỗi.</p></td>
</tr>
<tr>
<td style="text-align: center;"><p><strong>Luồng ngoại lệ</strong></p></td>
<td style="text-align: left;"><p>3a. Hộp thư hoặc số điện thoại trùng với tài khoản đã có: hệ thống từ chối và yêu cầu nhập lại.</p>
<p>3b. Chi nhánh được chỉ định đã ngừng hoạt động: hệ thống không cho gán.</p>
<p>4a. Cần tạo tài khoản khi chưa vào được giao diện web: quản trị viên dùng câu lệnh quản trị trên máy chủ để tạo tài khoản quản lý.</p></td>
</tr>
</tbody>
</table>

## 2.2. THIẾT KẾ VÀ XÂY DỰNG

### 2.2.1. Thiết kế kiểu dữ liệu

**a. Sơ đồ logic dữ liệu**


*Hình* 17*: Sơ đồ class.*

**b. Sơ đồ CSDL quan hệ**


*Hình* 18*: Sơ đồ cơ sở dữ liệu quan hệ.*

**c. Đặc tả các kiểu dữ liệu**

**- Bảng branches**

*Bảng* 22*: Đặc tả dữ liệu bảng branches.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh chi nhánh. |
| name | varchar(255) | Tên chi nhánh. |
| address | varchar(255) | Địa chỉ của chi nhánh. |
| phone | varchar(20) | Số điện thoại liên hệ. |
| lat | decimal(10,7) | Vĩ độ vị trí chi nhánh. |
| lng | decimal(10,7) | Kinh độ vị trí chi nhánh. |
| open_time | time | Giờ mở cửa. |
| close_time | time | Giờ đóng cửa. |
| slot_minutes | int unsigned | Số phút của một khung thời gian phục vụ. |
| max_orders_per_slot | int unsigned | Số đơn tối đa trong mỗi khung thời gian. |
| is_active | tinyint(1) | Cờ hoạt động: 1 là hoạt động, 0 là ngừng hoạt động. |
| image | varchar(255) | Đường dẫn ảnh chi nhánh. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |
| deleted_at | timestamp | Thời điểm xóa mềm bản ghi. |

**- Bảng users**

*Bảng* 23*: Đặc tả dữ liệu bảng users.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh người dùng. |
| name | varchar(255) | Họ và tên người dùng. |
| email | varchar(255) | Địa chỉ thư điện tử. |
| email_verified_at | timestamp | Thời điểm xác minh địa chỉ email. |
| password | varchar(255) | Mật khẩu được lưu dưới dạng băm. |
| phone | varchar(20) | Số điện thoại người dùng. |
| role | enum('customer', 'kitchen', 'staff', 'manager', 'admin') | Vai trò: khách hàng, đầu bếp, nhân viên, quản lý chi nhánh hoặc quản trị viên. |
| branch_id | bigint unsigned | Khóa ngoại tham chiếu branches.id; chi nhánh của người dùng. |
| address | text | Địa chỉ người dùng. |
| avatar | varchar(255) | Đường dẫn ảnh đại diện. |
| is_active | tinyint(1) | Cờ trạng thái hoạt động của tài khoản. |
| remember_token | varchar(100) | Mã ghi nhớ phiên đăng nhập. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |
| deleted_at | timestamp | Thời điểm xóa mềm bản ghi. |
| zalo_id | varchar(50) | Định danh tài khoản Zalo liên kết. |
| zalo_opted_in | tinyint(1) | Cờ ghi nhận người dùng đồng ý nhận thông tin qua Zalo. |
| google_id | varchar(255) | Định danh tài khoản Google liên kết để đăng nhập nhanh. |
| facebook_id | varchar(255) | Định danh tài khoản Facebook liên kết để đăng nhập nhanh. |

**- Bảng categories**

*Bảng* 24*: Đặc tả dữ liệu bảng categories.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh danh mục món ăn. |
| name | varchar(255) | Tên danh mục. |
| slug | varchar(255) | Chuỗi định danh dùng trong đường dẫn của danh mục. |
| icon | varchar(255) | Biểu tượng đại diện của danh mục. |
| sort_order | int unsigned | Thứ tự hiển thị danh mục. |
| is_active | tinyint(1) | Cờ cho phép danh mục hoạt động. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng menu_items**

*Bảng* 25*: Đặc tả dữ liệu bảng menu_items.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh món ăn. |
| category_id | bigint unsigned | Khóa ngoại tham chiếu categories.id; danh mục của món. |
| name | varchar(255) | Tên món ăn. |
| slug | varchar(255) | Chuỗi định danh dùng trong đường dẫn của món. |
| description | text | Mô tả chi tiết món ăn. |
| base_price | decimal(12,0) | Giá bán cơ sở của món, tính bằng đồng. |
| discount_percent | tinyint unsigned | Tỷ lệ giảm giá của món, tính theo phần trăm. |
| image | varchar(255) | Đường dẫn ảnh đại diện của món. |
| is_available | tinyint(1) | Cờ cho biết món có sẵn để bán. |
| tags | json | Danh sách nhãn của món ăn. |
| prep_time_minutes | int unsigned | Thời gian chuẩn bị món, tính bằng phút. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |
| deleted_at | timestamp | Thời điểm xóa mềm bản ghi. |

**- Bảng branch_menu_items**

*Bảng* 26*: Đặc tả dữ liệu bảng branch_menu_items.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính của bản ghi thực đơn chi nhánh. |
| branch_id | bigint unsigned | Khóa ngoại tham chiếu branches.id. |
| menu_item_id | bigint unsigned | Khóa ngoại tham chiếu menu_items.id. |
| price | decimal(12,0) | Giá bán của món tại chi nhánh, tính bằng đồng. |
| is_available | tinyint(1) | Trạng thái sẵn bán: 1 là có sẵn, 0 là không có sẵn. |
| is_bestseller | tinyint(1) | Nhãn Bán chạy của món tại chi nhánh. |
| is_chef_pick | tinyint(1) | Món được quản lý chọn vào mục Ưu đãi đặc biệt từ đầu bếp trên trang chủ. |
| stock_qty | int unsigned | Số lượng món còn tại chi nhánh; để trống là không giới hạn, 0 là hết món. Tự trừ khi đặt và hoàn lại khi huỷ đơn. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng orders**

*Bảng* 27*: Đặc tả dữ liệu bảng orders.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh đơn hàng. |
| order_code | varchar(255) | Mã đơn hàng hiển thị cho người dùng. |
| user_id | bigint unsigned | Khóa ngoại tham chiếu users.id; khách hàng đặt đơn. |
| branch_id | bigint unsigned | Khóa ngoại tham chiếu branches.id; chi nhánh xử lý đơn. |
| kitchen_by | bigint unsigned | Khóa ngoại tham chiếu users.id; nhân sự bếp được ghi nhận cho đơn. |
| type | enum('takeaway', 'delivery') | Hình thức nhận: mang đi hoặc giao tận nơi. |
| delivery_speed | enum('standard', 'express') | Tốc độ giao hàng: tiêu chuẩn hoặc hỏa tốc. |
| shipping_fee | decimal(12,0) | Phí giao hàng của đơn (15.000đ tiêu chuẩn, 30.000đ hỏa tốc, 0 với đơn mang đi). |
| status | enum('pending', 'confirmed', 'cooking', 'ready', 'completed', 'cancelled') | Trạng thái: chờ xác nhận, đã xác nhận, đang chế biến, sẵn sàng, hoàn thành hoặc đã hủy. |
| subtotal | decimal(12,0) | Tổng tiền món trước khi áp dụng giảm giá. |
| discount | decimal(12,0) | Số tiền giảm giá của đơn hàng. |
| total | decimal(12,0) | Tổng số tiền phải thanh toán của đơn hàng. |
| payment_method | varchar(20) | Phương thức thanh toán: cash (tiền mặt), bank (chuyển khoản VietQR), momo hoặc vnpay. |
| payment_status | enum('unpaid', 'paid') | Trạng thái thanh toán: chưa thanh toán hoặc đã thanh toán. |
| delivery_address | text | Thông tin địa chỉ giao hàng được lưu cho đơn. |
| estimated_time | timestamp | Thời điểm dự kiến hoàn tất hoặc nhận đơn. |
| pickup_code | varchar(10) | Mã dùng để nhận đơn mang đi. |
| coupon_id | bigint unsigned | Khóa ngoại tham chiếu coupons.id; mã giảm giá áp dụng cho đơn. |
| note | text | Ghi chú của đơn hàng. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng order_items**

*Bảng* 28*: Đặc tả dữ liệu bảng order_items.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh dòng chi tiết đơn hàng. |
| order_id | bigint unsigned | Khóa ngoại tham chiếu orders.id; đơn hàng chứa dòng chi tiết. |
| menu_item_id | bigint unsigned | Khóa ngoại tham chiếu menu_items.id; món được đặt. |
| name_snapshot | varchar(255) | Tên món được lưu tại thời điểm đặt hàng. |
| price_snapshot | decimal(12,0) | Đơn giá món được lưu tại thời điểm đặt hàng. |
| quantity | int unsigned | Số lượng món được đặt. |
| subtotal | decimal(12,0) | Thành tiền của dòng món: price_snapshot × quantity. |
| note | text | Ghi chú riêng cho món trong đơn hàng. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng coupons**

*Bảng* 29*: Đặc tả dữ liệu bảng coupons.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh mã giảm giá. |
| code | varchar(255) | Chuỗi mã giảm giá khách hàng sử dụng. |
| type | enum('percent', 'fixed') | Kiểu giảm: theo phần trăm hoặc theo số tiền cố định. |
| scope | enum('food', 'ship', 'express') | Phạm vi giảm: tiền món, phí giao tiêu chuẩn hoặc phí giao hỏa tốc. |
| description | varchar(255) | Mô tả ngắn của mã giảm giá hiển thị cho khách. |
| value | decimal(12,0) | Giá trị giảm: tỷ lệ phần trăm hoặc số tiền tùy theo type. |
| min_order_value | decimal(12,0) | Giá trị đơn hàng tối thiểu để áp dụng mã. |
| max_uses | int unsigned | Giới hạn tổng lượt sử dụng mã. |
| used_count | int unsigned | Số lượt mã đã được sử dụng. |
| max_uses_per_user | int unsigned | Giới hạn lượt sử dụng của mỗi người dùng. |
| is_active | tinyint(1) | Cờ cho phép mã giảm giá hoạt động. |
| started_at | timestamp | Thời điểm bắt đầu hiệu lực. |
| expired_at | timestamp | Thời điểm kết thúc hiệu lực. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng reviews**

*Bảng* 30*: Đặc tả dữ liệu bảng reviews.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh đánh giá. |
| order_id | bigint unsigned | Khóa ngoại tham chiếu orders.id; đơn hàng được đánh giá. |
| user_id | bigint unsigned | Khóa ngoại tham chiếu users.id; khách hàng viết đánh giá. |
| branch_id | bigint unsigned | Khóa ngoại tham chiếu branches.id; chi nhánh được đánh giá. |
| rating | tinyint unsigned | Số sao đánh giá đơn hàng, theo thang điểm 1–5. |
| delivery_rating | tinyint unsigned | Số sao đánh giá khâu giao hàng, theo thang điểm 1–5. |
| comment | text | Nội dung nhận xét của khách hàng. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng ai_chat_sessions**

*Bảng* 31*: Đặc tả dữ liệu bảng ai_chat_sessions.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh phiên trò chuyện với trợ lý AI. |
| user_id | bigint unsigned | Khóa ngoại tham chiếu users.id; người dùng tham gia hội thoại. |
| session_token | varchar(255) | Chuỗi định danh phiên hội thoại. |
| messages | json | Danh sách tin nhắn của người dùng và phản hồi của trợ lý AI. |
| context | json | Ngữ cảnh được lưu cho phiên hội thoại. |
| message_count | int unsigned | Số lượng tin nhắn được ghi nhận trong phiên. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng shifts**

*Bảng* 32*: Đặc tả dữ liệu bảng shifts.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh ca làm việc. |
| branch_id | bigint unsigned | Khóa ngoại tham chiếu branches.id; chi nhánh áp dụng ca làm. |
| name | varchar(255) | Tên ca làm việc. |
| start_time | time | Giờ bắt đầu ca. |
| end_time | time | Giờ kết thúc ca. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng attendances**

*Bảng* 33*: Đặc tả dữ liệu bảng attendances.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh bản ghi chấm công. |
| user_id | bigint unsigned | Khóa ngoại tham chiếu users.id; nhân sự được chấm công. |
| branch_id | bigint unsigned | Khóa ngoại tham chiếu branches.id; chi nhánh chấm công. |
| shift_id | bigint unsigned | Khóa ngoại tham chiếu shifts.id; ca làm việc tương ứng. |
| check_in | timestamp | Thời điểm chấm vào ca. |
| check_out | timestamp | Thời điểm chấm ra ca. |
| method | enum('face', 'manual') | Phương thức chấm công: nhận diện khuôn mặt hoặc thủ công. |
| face_confidence | decimal(5,2) | Giá trị độ tin cậy nhận diện khuôn mặt được lưu khi chấm công. |
| photo_path | varchar(255) | Đường dẫn ảnh minh chứng lúc chấm công, tự xoá sau 30 ngày. |
| late_reason | varchar(255) | Lý do đi trễ do nhân sự nhập khi trễ quá 5 phút. |
| early_reason | varchar(255) | Lý do ra ca sớm do nhân sự nhập khi sớm hơn 10 phút. |
| note | text | Ghi chú của bản ghi chấm công. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng payrolls**

*Bảng* 34*: Đặc tả dữ liệu bảng payrolls.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh bảng lương của nhân sự trong kỳ. |
| user_id | bigint unsigned | Khóa ngoại tham chiếu users.id; nhân sự nhận lương. |
| branch_id | bigint unsigned | Khóa ngoại tham chiếu branches.id; chi nhánh lập bảng lương. |
| month | tinyint unsigned | Tháng của kỳ lương, từ 1 đến 12. |
| year | smallint unsigned | Năm của kỳ lương. |
| total_hours | decimal(8,2) | Tổng số giờ công được tính trong kỳ. |
| total_days | int unsigned | Tổng số ngày công được tính trong kỳ. |
| base_salary | decimal(12,0) | Tiền lương cơ bản của kỳ trước khi cộng thưởng và trừ khấu trừ. |
| bonus | decimal(12,0) | Tổng tiền thưởng của kỳ. |
| deduction | decimal(12,0) | Tổng tiền khấu trừ của kỳ. |
| total | decimal(12,0) | Tiền thực nhận: base_salary + bonus − deduction. |
| status | enum('draft', 'confirmed', 'paid') | Trạng thái bảng lương: nháp, đã chốt hoặc đã trả. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng face_descriptors**

*Bảng* 35*: Đặc tả dữ liệu bảng face_descriptors.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh mẫu đặc trưng khuôn mặt. |
| user_id | bigint unsigned | Khóa ngoại tham chiếu users.id; người sở hữu mẫu khuôn mặt. |
| descriptor | json | Dữ liệu vectơ đặc trưng khuôn mặt phục vụ đối chiếu nhận diện. |
| created_at | timestamp | Thời điểm tạo bản ghi. |
| updated_at | timestamp | Thời điểm cập nhật bản ghi gần nhất. |

**- Bảng order_histories**

*Bảng 36: Đặc tả dữ liệu bảng order_histories.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh dòng nhật ký. |
| order_id | bigint unsigned | Khóa ngoại tham chiếu orders.id; đơn hàng được chuyển trạng thái. |
| from_status | enum(6 trạng thái đơn) | Trạng thái trước khi chuyển; rỗng với dòng tạo đơn. |
| to_status | enum(6 trạng thái đơn) | Trạng thái sau khi chuyển. |
| changed_by | bigint unsigned | Khóa ngoại tham chiếu users.id; người thực hiện. |
| note | text | Ghi chú, bắt buộc với lý do huỷ đơn. |
| created_at | timestamp | Thời điểm chuyển trạng thái. |

**- Bảng payroll_adjustments**

*Bảng 37: Đặc tả dữ liệu bảng payroll_adjustments.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh khoản điều chỉnh lương. |
| payroll_id | bigint unsigned | Khóa ngoại tham chiếu payrolls.id; bảng lương chứa khoản này. |
| type | enum('bonus', 'deduction') | Loại khoản: thưởng hoặc phạt. |
| amount | decimal(12,0) | Số tiền của khoản. |
| reason | varchar(255) | Lý do thưởng hoặc phạt. |
| created_by | bigint unsigned | Khóa ngoại tham chiếu users.id; người ghi khoản. |
| created_at | timestamp | Thời điểm tạo bản ghi. |

**- Bảng shift_assignments**

*Bảng 38: Đặc tả dữ liệu bảng shift_assignments.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh một lượt xếp lịch. |
| user_id | bigint unsigned | Khóa ngoại tham chiếu users.id; nhân viên hoặc đầu bếp được xếp. |
| shift_id | bigint unsigned | Khóa ngoại tham chiếu shifts.id; ca được xếp. |
| work_date | date | Ngày làm việc. Bộ (user_id, shift_id, work_date) là duy nhất; một người có thể có nhiều ca trong ngày. |
| created_at | timestamp | Thời điểm tạo bản ghi. |

**- Bảng kiosk_devices**

*Bảng 39: Đặc tả dữ liệu bảng kiosk_devices.*

| **Tên thuộc tính** | **Kiểu dữ liệu** | **Mô tả** |
|:--:|----|----|
| id | bigint unsigned | Khóa chính, định danh thiết bị chấm công. |
| branch_id | bigint unsigned | Khóa ngoại tham chiếu branches.id; chi nhánh sở hữu thiết bị. |
| name | varchar(100) | Tên thiết bị, ví dụ Quầy 1. |
| token_hash | varchar(64) | Mã băm SHA-256 của mã ghép thiết bị; mã gốc chỉ hiển thị một lần. |
| last_used_at | timestamp | Lần cuối thiết bị gọi lên máy chủ. |
| revoked_at | timestamp | Thời điểm quản lý thu hồi thiết bị; có giá trị thì thiết bị bị từ chối. |

## 2.3. GIAO DIỆN HỆ THỐNG

### 2.3.1. Giao diện phía khách hàng

**a. Giao diện trang chủ**


*Hình* 19*: Giao diện trang chủ.*

**b. Giao diện trang danh mục**


*Hình* 20*: Giao diện danh mục.*

**c. Giao diện đăng ký**


*Hình* 21*: Giao diện đăng ký.*

**d. Giao diện đăng nhập**


*Hình* 22*: Giao diện đăng nhập.*

**e. Giao diện menu khách hàng**


*Hình* 23*: Giao diện menu khách hàng.*

**f. Giao diện hồ sơ khách hàng**


*Hình* 24*: Giao diện hồ sơ khách hàng.*

**g. Giao diện đơn hàng**


*Hình* 25*: Giao diện đơn hàng.*

**h. Giao diện yêu thích**


*Hình* 26*: Giao diện trang yêu thích.*

**i. Giao diện thông báo khách hàng**


*Hình* 27*: Giao diện trang thông báo khách hàng.*

**j. Giao diện voucher**


*Hình* 28*: Giao diện voucher.*

**k. Giao diện địa chỉ**


*Hình* 29*: Giao diện địa chỉ.*

**l. Giao diện về chúng tôi**


*Hình* 30*: Giao diện về chúng tôi.*

**m. Giao diện hỗ trợ**


*Hình* 31*: Giao diện hỗ trợ.*

**n. Giao diện chính sách bảo mật**


*Hình* 32*: Giao diện chính sách bảo mật.*

**o. Giao diện chọn chi nhánh**


*Hình* 33*: Giao diện chọn chi nhánh.*

**p. Giao diện giỏ hàng**


*Hình* 34*: Giao diện giỏ hàng.*

**q. Giao diện thanh toán**


*Hình* 35*: Giao diện thanh toán.*

**r. Giao diện thanh toán thành công**


*Hình* 36*: Giao diện thanh toán thành công.*

**s. Giao diện tìm kiếm món**


*Hình* 37*: Giao diện tìm kiếm món ngay khi gõ.*

**t. Giao diện món bán chạy**


*Hình* 38*: Giao diện món bán chạy của chi nhánh.*

**u. Giao diện trợ lý chatbot**


*Hình* 39*: Giao diện trợ lý chatbot.*

**v. Giao diện trên điện thoại**


*Hình* 40*: Giao diện trên điện thoại.*

### 2.3.2. Giao diện quản lý chi nhánh

**a. Giao diện tổng quan chi nhánh**


*Hình* 41*: Giao diện tổng quan chi nhánh.*

**b. Giao diện thực đơn và giá bán chi nhánh**


*Hình* 42*: Giao diện thực đơn và giá bán chi nhánh.*

**c. Giao diện báo cáo doanh thu**


*Hình* 43*: Giao diện báo cáo doanh thu.*

### 2.3.3. Giao diện quản trị viên

**a. Giao diện tổng quan toàn chuỗi**


*Hình* 44*: Giao diện tổng quan toàn chuỗi.*

**b. Giao diện quản lý từ khoá chatbot**


*Hình* 45*: Giao diện quản lý từ khoá chatbot.*

### 2.3.4. Giao diện nhân viên

**a. Giao diện tổng quan**


*Hình* 46*: Giao diện tổng quan nhân viên.*

### 2.3.5. Giao diện đầu bếp

**a. Giao diện tổng quan**


*Hình* 47*: Giao diện tổng quan đầu bếp.*

**b. Giao diện màn hình bếp**


*Hình* 48*: Giao diện màn hình bếp.*

# CHƯƠNG 3: KẾT QUẢ VÀ PHƯƠNG HƯỚNG PHÁT TRIỂN

## 3.1. KẾT QUẢ ĐẠT ĐƯỢC

Sau quá trình phân tích, thiết kế và xây dựng, nhóm đã hoàn thiện các chức năng thuộc phạm vi triển khai của đề tài “Xây dựng web app cửa hàng bán đồ ăn nhanh – ZomZop”. Hệ thống phục vụ mô hình cửa hàng nhiều chi nhánh, kết hợp quy trình đặt món của khách hàng với công tác quản lý bán hàng và nhân sự. Sản phẩm hiện được cài đặt và vận hành trên môi trường cục bộ bằng Laragon, phục vụ việc thực hiện, kiểm tra và trình diễn đồ án.

### 3.1.1. Kết quả thực hiện các chức năng

Đối với khách hàng, hệ thống đã đáp ứng quy trình lựa chọn chi nhánh, tra cứu thực đơn, tìm kiếm món ăn và tạo đơn hàng. Thông tin món ăn được trình bày kèm hình ảnh, giá bán và tình trạng phục vụ tại chi nhánh đã chọn. Khách hàng có thể điều chỉnh giỏ hàng, ghi chú yêu cầu và lựa chọn nhận món mang đi hoặc giao tận nơi. Với đơn giao hàng, thông tin người nhận được lấy từ sổ địa chỉ; trường hợp chưa có địa chỉ, khách hàng cần bổ sung trước khi tiếp tục đặt hàng.

Chức năng mã giảm giá cho phép khách hàng lựa chọn mã trong danh sách hoặc nhập mã để áp dụng. Hệ thống kiểm tra điều kiện sử dụng, thời hạn và giới hạn lượt dùng, đồng thời xác định số tiền được giảm theo phạm vi của từng mã. Tiền món, phí giao hàng, khoản giảm giá và tổng số tiền phải trả được thể hiện trên trang thanh toán. Khách hàng chọn chuyển khoản được hiển thị mã VietQR để quét và quản lý xác nhận khi nhận tiền. Phần lựa chọn MoMo và VNPay hiện ghi nhận phương thức thanh toán của đơn hàng; việc kết nối và xác nhận giao dịch qua cổng thanh toán điện tử chưa thuộc kết quả triển khai của giai đoạn này.

Sau khi đặt hàng, khách hàng có thể xem lịch sử và tiến trình xử lý đơn, đặt lại đơn cũ theo giá hiện hành và gửi đánh giá khi đơn hoàn thành. Hồ sơ cá nhân, địa chỉ giao hàng và danh sách món yêu thích được quản lý trong khu vực tài khoản. Các chức năng này tạo sự liên tục giữa quá trình đặt món và việc theo dõi, phản hồi sau khi mua hàng.

Đối với quản lý chi nhánh, hệ thống đã xây dựng các chức năng tiếp nhận, duyệt, hủy và cập nhật trạng thái đơn hàng theo trình tự xử lý. Nhật ký chuyển trạng thái được lưu để phục vụ tra cứu. Quản lý được điều chỉnh thực đơn, giá bán, số lượng tồn và tình trạng còn hoặc hết món trong phạm vi chi nhánh được phân công. Báo cáo doanh thu tổng hợp dữ liệu đơn hàng theo thời gian, phương thức thanh toán và các món bán chạy; kết quả được trình bày bằng bảng, biểu đồ và có thể xuất ra Excel. Quản lý cũng có thể xem và phản hồi đánh giá của khách hàng.

Về quản lý nhân sự, hệ thống hỗ trợ tạo tài khoản nhân viên và đầu bếp, thiết lập ca làm việc, đăng ký mẫu khuôn mặt và ghi nhận chấm công. Nhân sự thực hiện chấm vào, chấm ra tại thiết bị có camera; quản lý được bổ sung bản ghi thủ công trong trường hợp cần xử lý ngoại lệ. Dữ liệu giờ công được sử dụng để tính lương, kết hợp các khoản thưởng, khấu trừ và trạng thái chốt bảng lương. Việc liên kết dữ liệu chấm công với bảng lương giúp người quản lý có căn cứ đối chiếu khi tổng hợp thu nhập của từng nhân sự.

Đối với quản trị viên, hệ thống cung cấp khu vực quản lý dữ liệu chung của toàn chuỗi, bao gồm chi nhánh, tài khoản quản lý, danh mục, món ăn, hình ảnh, banner, mã giảm giá và các thiết lập liên quan. Quản trị viên có thể theo dõi số liệu tổng hợp, quản lý từ khóa của trợ lý AI và tiếp nhận thông tin liên hệ. Cơ chế phân quyền được tổ chức theo năm vai trò: khách hàng, quản lý chi nhánh, nhân viên, đầu bếp và quản trị viên. Trong đó, nhân viên và đầu bếp sử dụng các chức năng định danh, chấm công; nghiệp vụ điều phối đơn hàng do quản lý chi nhánh thực hiện.

Trợ lý AI đã được tích hợp để hỗ trợ khách hàng tra cứu món ăn, giá bán và cách đặt món. Hệ thống ưu tiên câu trả lời theo bộ từ khóa được quản trị viên cấu hình; các câu hỏi khác được xử lý thông qua Google Gemini với thông tin thực đơn của chi nhánh. Đây là chức năng hỗ trợ tư vấn, không thay thế bước kiểm tra giá, tình trạng món và tổng tiền khi xác nhận đơn hàng. Phần nhận diện khuôn mặt sử dụng thư viện face-api để phục vụ chấm công trên trình duyệt \[6\], \[7\].

Về tổ chức chương trình, hệ thống sử dụng PHP và Laravel cho xử lý nghiệp vụ, MySQL để lưu trữ dữ liệu, Blade kết hợp Tailwind CSS và JavaScript để xây dựng giao diện \[7\]–\[10\]. Cấu trúc cơ sở dữ liệu và dữ liệu khởi tạo được quản lý thông qua migrations và seeders. Mã nguồn được lưu trữ trên GitHub để hỗ trợ phối hợp giữa các thành viên. Trong quá trình kiểm tra và sửa lỗi, nhóm sử dụng Claude để hỗ trợ rà soát mã nguồn và phân tích các vấn đề phát sinh. Kết quả hiện tại được xác định trong phạm vi môi trường Laragon; chưa sử dụng số liệu định lượng để kết luận về khả năng chịu tải hoặc mức độ an toàn của hệ thống khi vận hành thực tế.

### 3.1.2. Kết quả kiểm thử

Bên cạnh việc chạy thử thủ công theo từng vai trò, nhóm xây dựng bộ kiểm thử tự động bằng PHPUnit cho các nghiệp vụ dễ sai sót. Tại thời điểm hoàn thiện báo cáo, bộ kiểm thử gồm 367 ca kiểm thử với 1.517 phép kiểm tra, chạy trên cơ sở dữ liệu SQLite trong bộ nhớ nên không ảnh hưởng dữ liệu thật; toàn bộ đều đạt. Bảng 40 trình bày một số kịch bản tiêu biểu.

*Bảng 40: Một số kịch bản kiểm thử tiêu biểu.*

| **Kịch bản kiểm thử** | **Kết quả mong đợi** | **Kết quả** |
|:--:|----|----|
| Chuyển đơn từ chờ xác nhận sang sẵn sàng (bỏ qua bước) | Hệ thống từ chối, giữ nguyên trạng thái | Đạt |
| Huỷ đơn đang nấu | Không cho huỷ; chỉ huỷ được khi chờ xác nhận hoặc đã xác nhận | Đạt |
| Huỷ đơn có dùng mã giảm giá và trừ tồn | Hoàn lại lượt dùng mã và số lượng tồn đúng một lần | Đạt |
| Đặt món vượt số lượng tồn | Không tạo đơn, báo khách món không đủ | Đạt |
| Quản lý mở đơn hoặc nhân viên của chi nhánh khác | Trả về không tìm thấy (404) | Đạt |
| Nhân viên chi nhánh khác chấm công tại máy của chi nhánh | Không nhận diện, không ghi công | Đạt |
| Chấm vào trễ hơn 5 phút | Yêu cầu nhập lý do rồi mới ghi nhận | Đạt |
| Chấm ra sau nửa đêm với ca qua đêm | Không bị tính là ra sớm, giờ làm tính đúng | Đạt |
| Tính lương nhân sự trong 7 ngày thử việc | Áp dụng đơn giá thử việc cho các ngày đó | Đạt |
| Xếp lịch cho người hoặc ca của chi nhánh khác | Bỏ qua, không lưu | Đạt |

Các kết quả trên được kiểm tra trên môi trường cục bộ; nhóm chưa đo hiệu năng khi nhiều người dùng truy cập đồng thời.

### 3.1.3. Kết luận chung

Đề tài đã hình thành một ứng dụng web có sự liên kết giữa hoạt động bán hàng trực tuyến và quản lý nội bộ theo chi nhánh. Khách hàng thực hiện các thao tác đặt món và theo dõi đơn trên cùng một tài khoản; quản lý chi nhánh sử dụng dữ liệu đơn hàng, giờ công và bảng lương để theo dõi hoạt động; quản trị viên quản lý dữ liệu chung của toàn hệ thống. Kết quả này đáp ứng mục tiêu xây dựng sản phẩm trong phạm vi đồ án và tạo cơ sở để tiếp tục hoàn thiện trước khi áp dụng tại cửa hàng.

Quá trình thực hiện giúp các thành viên củng cố kiến thức về phân tích yêu cầu, mô hình hóa nghiệp vụ, thiết kế cơ sở dữ liệu, lập trình web và quản lý phiên bản mã nguồn. Việc tích hợp đặt hàng, voucher, chấm công và tính lương cũng cho thấy sự cần thiết của việc thống nhất quy tắc xử lý giữa các phân hệ. Những thay đổi về chức năng cần được cập nhật đồng thời trong chương trình, sơ đồ dữ liệu và báo cáo để bảo đảm tính nhất quán của sản phẩm.

Bên cạnh kết quả đạt được, hệ thống còn một số giới hạn về phạm vi và điều kiện triển khai. Sản phẩm mới vận hành trên máy chủ cục bộ, chưa được đánh giá trong điều kiện cửa hàng hoạt động thực tế với nhiều người dùng truy cập đồng thời. Thanh toán điện tử chưa kết nối giao dịch thực; phí giao hàng còn áp dụng mức cố định; hệ thống chưa điều phối người giao hàng và chưa quản lý nguyên liệu đầu vào. Nhận diện khuôn mặt cần tiếp tục được đánh giá với các điều kiện ánh sáng, camera và mẫu đăng ký khác nhau. Đối với trợ lý AI, chất lượng phản hồi cần được theo dõi vì thông tin thực đơn cung cấp cho mô hình không loại trừ hoàn toàn khả năng trả lời sai.

## 3.2. PHƯƠNG HƯỚNG PHÁT TRIỂN

Trên cơ sở kết quả và những giới hạn đã xác định, việc phát triển ZomZop trong giai đoạn tiếp theo cần ưu tiên khả năng vận hành thực tế và tính chính xác của dữ liệu. Các chức năng mở rộng chỉ nên triển khai sau khi quy trình đặt hàng, phân quyền, chấm công và tính lương được kiểm tra đầy đủ.

Trước hết, nhóm định hướng triển khai hệ thống lên máy chủ có tên miền và kết nối HTTPS để tổ chức vận hành thử tại một chi nhánh. Giai đoạn này cần bổ sung các kịch bản kiểm thử có đầu vào, kết quả mong đợi và kết quả thực tế; kiểm tra quyền truy cập giữa các chi nhánh, xử lý nhiều đơn cùng thời điểm, cập nhật tồn món và giới hạn sử dụng voucher. Đồng thời, cần thiết lập sao lưu, phục hồi dữ liệu, ghi nhận lỗi và theo dõi hiệu năng để có căn cứ đánh giá trước khi mở rộng số lượng người dùng.

Đối với thanh toán, hướng phát triển là tích hợp MoMo và VNPay để xử lý giao dịch trực tuyến, tiếp nhận kết quả từ nhà cung cấp và cập nhật trạng thái thanh toán. Quy trình cần xử lý được các trường hợp thanh toán thất bại, người dùng thoát giữa chừng hoặc kết quả được gửi lại nhiều lần. Việc đối soát và hoàn tiền khi hủy đơn phải gắn với trạng thái giao dịch thực tế, tránh ghi nhận trùng hoặc sai số tiền.

Đối với vận hành cửa hàng, nhóm dự kiến bổ sung thông báo tức thời khi có đơn mới cho màn hình bếp hiện có, cho phép bếp tự cập nhật trạng thái đang nấu và sẵn sàng và cơ chế phối hợp giữa khâu chế biến với giao nhận. Chức năng giao hàng có thể được mở rộng bằng bản đồ, tính phí theo khoảng cách và phân công người giao hàng. Về quản lý hàng hóa, cần phát triển dữ liệu nguyên liệu, định mức sử dụng theo món và nghiệp vụ nhập, xuất kho để theo dõi lượng tồn sát hơn với hoạt động chế biến.

Đối với chấm công và quản lý nhân sự, cần tăng cường khả năng phát hiện hành vi sử dụng ảnh hoặc video để giả mạo, đồng thời đánh giá độ chính xác trên các thiết bị khác nhau. Việc thu thập, lưu trữ và xóa dữ liệu khuôn mặt cần có quy trình rõ ràng, giới hạn người được truy cập và ghi nhận lịch sử thay đổi. Các quy tắc tính giờ công, làm thêm giờ, nghỉ phép và điều chỉnh lương cũng cần được mở rộng phù hợp với quy chế của đơn vị sử dụng.

Đối với trợ lý AI, nhóm định hướng hoàn thiện bộ câu hỏi thường gặp, kiểm soát phạm vi tư vấn và cập nhật thông tin món ăn khi thực đơn thay đổi. Hệ thống cần có phương án xử lý khi dịch vụ AI không phản hồi và cho phép chuyển yêu cầu sang nhân viên hỗ trợ. Khi tích lũy đủ dữ liệu bán hàng, có thể nghiên cứu dự báo nhu cầu theo món, theo khung giờ và đề xuất món phù hợp với lịch sử mua hàng; các kết quả này phải được đánh giá trước khi sử dụng để hỗ trợ quyết định kinh doanh.

Cuối cùng, nhóm tiếp tục cải thiện giao diện trên điện thoại, khả năng truy cập và các thông báo hướng dẫn thao tác. Việc phát triển ứng dụng di động riêng sẽ được xem xét sau khi phiên bản web vận hành ổn định và có nhu cầu sử dụng rõ ràng. Thứ tự triển khai các nội dung trên có thể điều chỉnh theo kết quả vận hành thử, nguồn lực của nhóm và phản hồi từ người sử dụng.

# TÀI LIỆU THAM KHẢO

\[1\] Trần Huy Long, Trần Thị Diễm Trang, Phát triển ứng dụng Web - Đại học Tiền Giang (2014 - 2015).

\[2\] Đặng Quốc Bảo và các đồng tác giả, Giáo trình Cơ sở dữ liệu (2020).

\[3\] Trần Thị Minh Châu, Công nghệ phần mềm - tài liệu học phần, Đại học Công nghệ, ĐHQGHN (2015).

\[4\] Lê Thị Ngọc Diệp, Bài giảng Phân tích và thiết kế hệ thống thương mại điện tử,

\[5\] Trần Thị Thập, Bài giảng Xây dựng Website Thương mại điện tử (2022).

\[6\] Trần Quốc Trung, Nguyễn Thị Kim Ngân, Bài giảng học phần Thiết kế giao diện người dùng (2022).

\[7\] The PHP Group, *PHP Manual*, https://www.php.net/manual/en/ (truy cập ngày 16/10/2026).

\[8\] Laravel, *Laravel Documentation*, https://laravel.com/docs (truy cập ngày 16/10/2026).

\[9\] Oracle, *MySQL Documentation*, https://dev.mysql.com/doc/ (truy cập ngày 16/10/2026).

\[10\] Tailwind Labs, *Tailwind CSS Documentation*, https://tailwindcss.com/docs (truy cập ngày 16/10/2026).

\[11\] Vladimir Mandic, *@vladmandic/face-api*, https://github.com/vladmandic/face-api (truy cập ngày 24/10/2026).

\[12\] Google, *Gemini API Documentation*, https://ai.google.dev/gemini-api/docs (truy cập ngày 24/10/2026).

\[13\] ApexCharts, *ApexCharts Documentation*, https://apexcharts.com/docs/ (truy cập ngày 24/10/2026).

\[14\] PHPOffice, *PhpSpreadsheet Documentation*, https://phpspreadsheet.readthedocs.io/ (truy cập ngày 24/10/2026).
