<?php

namespace App\Enums;

enum PolicyType: string
{
    case NAT_VALUES_LOYALTY = 'NAT_VALUES_LOYALTY';
    case CIVIC_HISTORY_GEO = 'CIVIC_HISTORY_GEO';
    case EDU_INNOV_TECH = 'EDU_INNOV_TECH';
    case READING_CULTURE = 'READING_CULTURE';
    case STUDENT_DEVELOPMENT = 'STUDENT_DEVELOPMENT';
    case SPECIAL_NEEDS_EDU = 'SPECIAL_NEEDS_EDU';
    case PERSONAL_EXCELLENCE = 'PERSONAL_EXCELLENCE';
    case SCHOOL_SAFETY = 'SCHOOL_SAFETY';
    case EDU_EQUITY_ACCESS = 'EDU_EQUITY_ACCESS';
    case TEACHER_UPSKILL = 'TEACHER_UPSKILL';
    case PERSONALIZED_ASSESSMENT = 'PERSONALIZED_ASSESSMENT';
    case SMART_GOVERNANCE = 'SMART_GOVERNANCE';
    case REDUCE_TEACHER_WORKLOAD = 'REDUCE_TEACHER_WORKLOAD';
    case TEACHER_WELFARE = 'TEACHER_WELFARE';
    case MORAL_QUALITY_LEARNING = 'MORAL_QUALITY_LEARNING';

    public function label(): string
    {
        return match($this) {
            self::NAT_VALUES_LOYALTY => 'คุณธรรม จริยธรรม ความเป็นไทย และความภาคภูมิใจในความเป็นไทย',
            self::CIVIC_HISTORY_GEO => 'หน้าที่พลเมือง ประวัติศาสตร์ และภูมิศาสตร์',
            self::EDU_INNOV_TECH => 'การศึกษาเพื่อการพัฒนาทักษะในศตวรรษที่ 21 และนวัตกรรมเทคโนโลยี',
            self::READING_CULTURE => 'การส่งเสริมการอ่านและวัฒนธรรมการอ่าน',
            self::STUDENT_DEVELOPMENT => 'การพัฒนาผู้เรียนให้มีคุณภาพตามมาตรฐานการศึกษา',
            self::SPECIAL_NEEDS_EDU => 'การจัดการศึกษาสำหรับผู้เรียนที่มีความต้องการพิเศษ',
            self::PERSONAL_EXCELLENCE => 'การส่งเสริมความเป็นเลิศของผู้เรียน',
            self::SCHOOL_SAFETY => 'ความปลอดภัยในสถานศึกษา',
            self::EDU_EQUITY_ACCESS => 'ความเสมอภาคทางการศึกษาและการเข้าถึงการศึกษา',
            self::TEACHER_UPSKILL => 'การพัฒนาครูและบุคลากรทางการศึกษา',
            self::PERSONALIZED_ASSESSMENT => 'การประเมินผลการเรียนรู้ที่หลากหลายและเหมาะสมกับผู้เรียน',
            self::SMART_GOVERNANCE => 'การบริหารจัดการสถานศึกษาอย่างมีประสิทธิภาพ',
            self::REDUCE_TEACHER_WORKLOAD => 'การลดภาระงานครู',
            self::TEACHER_WELFARE => 'สวัสดิการครูและบุคลากรทางการศึกษา',
            self::MORAL_QUALITY_LEARNING => 'คุณภาพการเรียนรู้ที่เน้นคุณธรรม',
        };
    }
}
