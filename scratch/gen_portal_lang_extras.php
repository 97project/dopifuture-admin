<?php

$solutionsPath = dirname(__DIR__) . '/resources/views/portal/solutions.blade.php';
$contactPath = dirname(__DIR__) . '/resources/views/portal/contact.blade.php';

$solContent = file_get_contents($solutionsPath);
$conContent = file_get_contents($contactPath);

$solReplacements = [
    "app()->getLocale() === 'tr' ? 'Çözümlerimiz — DopiFuture' : 'Solutions — DopiFuture'" => "__('portal.sol_title')",
    "app()->getLocale() === 'tr' ? 'DopiFuture dijital eğitim uygulamaları' : 'DopiFuture digital education applications'" => "__('portal.sol_meta')",
    "@if(app()->getLocale() === 'tr')
            Beş Güçlü Uygulama,<br><span>Tek Ekosistem</span>
        @else
            Five Powerful Apps,<br><span>One Ecosystem</span>
        @endif" => "{!! __('portal.sol_hero_title') !!}",
    "@if(app()->getLocale() === 'tr')
            DopiFuture, simülasyon tabanlı oyunlardan AI koçluğa, girişimcilik laboratuvarından interaktif öğrenme alanlarına kadar kapsamlı bir eğitim ekosistemi sunar.
        @else
            DopiFuture offers a comprehensive education ecosystem — from simulation-based games to AI coaching, entrepreneurship labs, and interactive learning spaces.
        @endif" => "{{ __('portal.sol_hero_desc') }}",
    "@if(app()->getLocale() === 'tr')
                Multiplayer simülasyon tabanlı karar verme platformu. Öğrenciler deprem müdahalesi, diplomatik kriz yönetimi, çevre politikaları gibi gerçekçi senaryolarda ekip olarak rol dağılımı yapar ve kritik kararlar alır. Her karar dallanma hikayesi oluşturur ve nihai skor hesaplanır.
            @else
                A multiplayer simulation-based decision-making platform. Students team up for realistic scenarios like earthquake response, diplomatic crisis management, and environmental policies. They assign roles, make critical decisions that branch the story, and earn a final score.
            @endif" => "{{ __('portal.sol_mw_desc') }}",
    "app()->getLocale() === 'tr' ? '4 oyunculu multiplayer oturumlar' : '4-player multiplayer sessions'" => "__('portal.sol_f1_1')",
    "app()->getLocale() === 'tr' ? 'Rol bazlı karar mekanizması' : 'Role-based decision mechanism'" => "__('portal.sol_f1_2')",
    "app()->getLocale() === 'tr' ? 'Gerçek zamanlı skor tablosu' : 'Real-time score tracking'" => "__('portal.sol_f1_3')",
    "app()->getLocale() === 'tr' ? 'Öğretmen görev ataması' : 'Teacher assignment management'" => "__('portal.sol_f1_4')",
    "@if(app()->getLocale() === 'tr')
                Girişimcilik eğitiminin dijital laboratuvarı. Öğrenciler fikir geliştirmeden pitch sunumuna kadar bir startup yolculuğunu deneyimler. Her adımda dosya yükleyebilir, yapay zekâ değerlendirmesi alabilir ve puan kazanabilir. Zorluk seviyeleri ve kilitli adımlar ile progresif öğrenme.
            @else
                The digital laboratory for entrepreneurship education. Students experience the startup journey from ideation to pitch deck. Each step allows file uploads, AI-powered evaluations, and point earnings. Progressive learning with difficulty levels and locked steps.
            @endif" => "{{ __('portal.sol_su_desc') }}",
    "app()->getLocale() === 'tr' ? 'Adım adım girişimcilik müfredatı' : 'Step-by-step entrepreneurship curriculum'" => "__('portal.sol_f2_1')",
    "app()->getLocale() === 'tr' ? 'AI destekli değerlendirme' : 'AI-powered evaluation'" => "__('portal.sol_f2_2')",
    "app()->getLocale() === 'tr' ? 'Dosya yükleme & portfolio' : 'File upload & portfolio'" => "__('portal.sol_f2_3')",
    "app()->getLocale() === 'tr' ? 'Zorluk seviyeli görevler' : 'Difficulty-leveled tasks'" => "__('portal.sol_f2_4')",
    "@if(app()->getLocale() === 'tr')
                Yapay zekâ ile desteklenen kariyer keşif simülatörü. Öğrenciler mühendis, doktor, avukat, girişimci gibi rollere bürünerek gerçekçi iş senaryolarında kararlar alır. Her seçim farklı bir hikaye dalı oluşturur — böylece her deneyim benzersiz ve öğreticidir.
            @else
                An AI-powered career discovery simulator. Students step into roles such as engineer, doctor, lawyer, and entrepreneur, making decisions in realistic work scenarios. Each choice creates a different story branch — making every experience unique and educational.
            @endif" => "{{ __('portal.sol_rg_desc') }}",
    "app()->getLocale() === 'tr' ? 'AI tarafından dinamik hikaye dalları' : 'AI-generated dynamic story branches'" => "__('portal.sol_f3_1')",
    "app()->getLocale() === 'tr' ? '10+ farklı meslek senaryosu' : '10+ different career scenarios'" => "__('portal.sol_f3_2')",
    "app()->getLocale() === 'tr' ? 'Karar ağacı ve sonuç analizi' : 'Decision tree & outcome analysis'" => "__('portal.sol_f3_3')",
    "app()->getLocale() === 'tr' ? 'Tekrarlanabilir farklı sonuçlar' : 'Replayable with different outcomes'" => "__('portal.sol_f3_4')",
    "@if(app()->getLocale() === 'tr')
                Kişiselleştirilmiş yapay zekâ koçluk platformu. Her öğrencinin ilgi alanları, güçlü ve zayıf yönleri dikkate alınarak özelleştirilmiş sohbetler oluşturur. Laravel WebSocket altyapısı ile gerçek zamanlı iletişim ve akıcı sohbet deneyimi sunar.
            @else
                A personalized AI coaching platform. Creates customized conversations based on each student's interests, strengths, and areas for growth. Delivers real-time communication and a fluid chat experience via Laravel WebSocket infrastructure.
            @endif" => "{{ __('portal.sol_coach_desc') }}",
    "app()->getLocale() === 'tr' ? 'Profil bazlı kişiselleştirme' : 'Profile-based personalization'" => "__('portal.sol_f4_1')",
    "app()->getLocale() === 'tr' ? 'Gerçek zamanlı WebSocket sohbet' : 'Real-time WebSocket chat'" => "__('portal.sol_f4_2')",
    "app()->getLocale() === 'tr' ? 'Oturum geçmişi ve takip' : 'Session history & tracking'" => "__('portal.sol_f4_3')",
    "app()->getLocale() === 'tr' ? 'Çok dilli AI asistan' : 'Multilingual AI assistant'" => "__('portal.sol_f4_4')",
    "@if(app()->getLocale() === 'tr')
                Yapay zekâ destekli öğretim asistanı platformu. Öğrenciler istedikleri konuda soru sorabilir, ders seçebilir, sınıf seviyesine göre özelleştirilmiş interaktif dersler alabilir. Detaylı oturum geçmişi ile öğrenme yolculuğunu takip eder.
            @else
                An AI-powered teaching assistant platform. Students can ask questions on any topic, select subjects, and take interactive lessons customized to their grade level. Tracks the learning journey with detailed session history.
            @endif" => "{{ __('portal.sol_study_desc') }}",
    "app()->getLocale() === 'tr' ? 'Konu & ders bazlı öğrenme' : 'Subject & topic-based learning'" => "__('portal.sol_f5_1')",
    "app()->getLocale() === 'tr' ? 'Sınıf seviyesi ayarı' : 'Grade-level adjustment'" => "__('portal.sol_f5_2')",
    "app()->getLocale() === 'tr' ? 'İnteraktif soru-cevap' : 'Interactive Q&A sessions'" => "__('portal.sol_f5_3')",
    "app()->getLocale() === 'tr' ? 'Detaylı oturum geçmişi' : 'Detailed session history'" => "__('portal.sol_f5_4')",
    "@if(app()->getLocale() === 'tr')
            Tüm Uygulamaları Keşfedin
        @else
            Explore All Applications
        @endif" => "{{ __('portal.sol_cta_title') }}",
    "@if(app()->getLocale() === 'tr')
            Ücretsiz kayıt olun ve DopiFuture ekosisteminin tüm gücünü okulunuza entegre edin.
        @else
            Register for free and integrate the full power of the DopiFuture ecosystem into your school.
        @endif" => "{{ __('portal.sol_cta_desc') }}",
    "app()->getLocale() === 'tr' ? 'Hemen Başla' : 'Get Started Free'" => "__('portal.hero_btn_start')"
];

$conReplacements = [
    "app()->getLocale() === 'tr' ? 'İletişim — DopiFuture' : 'Contact — DopiFuture'" => "__('portal.contact_title')",
    "app()->getLocale() === 'tr' ? 'DopiFuture ile iletişime geçin' : 'Get in touch with DopiFuture'" => "__('portal.contact_meta')",
    "@if(app()->getLocale() === 'tr')
            Bizimle İletişime<br><span>Geçin</span>
        @else
            Get in <span>Touch</span><br>with Us
        @endif" => "{!! __('portal.contact_hero_title') !!}",
    "@if(app()->getLocale() === 'tr')
            Sorularınız, önerileriniz veya işbirlikleriniz için bize ulaşın. Ekibimiz size yardımcı olmaktan memnuniyet duyacaktır.
        @else
            Contact us for your questions, suggestions, or collaborations. Our team will be happy to assist you.
        @endif" => "{{ __('portal.contact_hero_desc') }}",
    "app()->getLocale() === 'tr' ? 'Adres' : 'Address'" => "__('portal.contact_address_lbl')",
    "app()->getLocale() === 'tr' ? 'Çalışma Saatleri' : 'Working Hours'" => "__('portal.contact_hours_lbl')",
    "app()->getLocale() === 'tr' ? 'Pazartesi – Cuma, 09:00 – 18:00' : 'Monday – Friday, 09:00 – 18:00'" => "__('portal.contact_hours_val')",
    "app()->getLocale() === 'tr' ? 'Sosyal Medya' : 'Social Media'" => "__('portal.contact_social_lbl')",
    "app()->getLocale() === 'tr' ? 'Mesaj Gönderin' : 'Send a Message'" => "__('portal.contact_form_title')",
    "app()->getLocale() === 'tr' ? 'Adınız' : 'Your Name'" => "__('portal.contact_name_lbl')",
    "app()->getLocale() === 'tr' ? 'Ad Soyad' : 'Full name'" => "__('portal.contact_name_ph')",
    "app()->getLocale() === 'tr' ? 'Konu' : 'Subject'" => "__('portal.contact_subj_lbl')",
    "app()->getLocale() === 'tr' ? 'Mesajınızın konusu' : 'Subject of your message'" => "__('portal.contact_subj_ph')",
    "app()->getLocale() === 'tr' ? 'Mesaj' : 'Message'" => "__('portal.contact_msg_lbl')",
    "app()->getLocale() === 'tr' ? 'Mesajınızı buraya yazın...' : 'Write your message here...'" => "__('portal.contact_msg_ph')",
    "app()->getLocale() === 'tr' ? 'Mesaj Gönder' : 'Send Message'" => "__('portal.contact_btn_send')",
    "@if(app()->getLocale() === 'tr')
            Yardıma mı İhtiyacınız Var?
        @else
            Need Help?
        @endif" => "{{ __('portal.contact_help_title') }}",
    "@if(app()->getLocale() === 'tr')
            Destek merkezimizi ziyaret edebilir veya kullanım kılavuzlarına göz atabilirsiniz.
        @else
            You can visit our support center or browse the user guides.
        @endif" => "{{ __('portal.contact_help_desc') }}"
];

$solContent = str_replace(array_keys($solReplacements), array_values($solReplacements), $solContent);
$conContent = str_replace(array_keys($conReplacements), array_values($conReplacements), $conContent);

file_put_contents($solutionsPath, $solContent);
file_put_contents($contactPath, $conContent);
echo "Solutions and Contact blade files refactored with translations keys.\n";
