import sys
import io

# تغيير ترميز المخرجات ليدعم العربية
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

def main():
    # التأكد من وجود مدخلات
    if len(sys.argv) > 1:
        name = sys.argv[1]
        
        # لنقم ببعض العمليات "البرمجية"
        upper_name = name.upper()
        name_length = len(name)
        
        # طباعة النتيجة (هذا ما سيستلمه PHP)
        print(f"تمت المعالجة بنجاح!")
        print(f"الاسم بالأحرف الكبيرة: {upper_name}")
        print(f"عدد حروف الاسم: {name_length}")
    else:
        print("خطأ: لم يتم استقبال أي بيانات.")

if __name__ == "__main__":
    main()