SET NAMES utf8mb4;

-- 사용자 6명 (일반 5 + admin 1, admin이 id=1)
INSERT INTO users (username, password, email, nickname, bio, is_admin) VALUES
('admin', 'admin1234!', 'admin@target.local', '관리자', '이 사이트를 운영하고 있습니다.', 1),
('hyunwoo_seo', 'hyunwoo1234', 'hyunwoo_seo@target.local', '서현우', '안녕하세요, 서현우입니다. 여행과 사진을 좋아해요.', 0),
('soojin_lee', 'soojin1234', 'soojin_lee@target.local', '이수진', '개발자 이수진입니다. 커피 없이는 못 살아요.', 0),
('jihoon_park', 'jihoon1234', 'jihoon_park@target.local', '박지훈', '독서와 영화 감상이 취미입니다.', 0),
('yuna_choi', 'yuna1234', 'yuna_choi@target.local', '최유나', '게임 좋아하는 최유나입니다.', 0),
('doyoon_jang', 'doyoon1234', 'doyoon_jang@target.local', '장도윤', '요리와 베이킹에 관심이 많아요.', 0);

-- 게시글 25개 (free/notice 분산) 및 댓글 2~5개씩 생성
DELIMITER $$
CREATE PROCEDURE seed_posts_and_comments()
BEGIN
  DECLARE i INT DEFAULT 1;
  DECLARE j INT DEFAULT 0;
  DECLARE c INT DEFAULT 0;
  DECLARE post_user INT;
  DECLARE cat VARCHAR(20);
  DECLARE post_id_val INT;

  WHILE i <= 25 DO
    SET post_user = 1 + FLOOR(RAND() * 6);
    IF i <= 3 THEN
      SET cat = 'notice';
    ELSE
      SET cat = IF(RAND() < 0.3, 'notice', 'free');
    END IF;

    INSERT INTO posts (user_id, title, content, category, view_count, created_at)
    VALUES (
      post_user,
      CONCAT(IF(cat = 'notice', '[공지] ', ''), '게시글 제목 ', i),
      CONCAT('이것은 ', i, '번째 게시글의 본문 내용입니다. 자유롭게 이야기를 나눠보세요.'),
      cat,
      FLOOR(RAND() * 200),
      DATE_SUB(NOW(), INTERVAL (25 - i) DAY)
    );

    SET post_id_val = LAST_INSERT_ID();
    SET c = 2 + FLOOR(RAND() * 4); -- 2~5개
    SET j = 0;
    WHILE j < c DO
      INSERT INTO comments (post_id, user_id, content, created_at)
      VALUES (
        post_id_val,
        1 + FLOOR(RAND() * 6),
        CONCAT('댓글 ', j + 1, '번째입니다. 좋은 글 감사합니다.'),
        DATE_SUB(NOW(), INTERVAL (25 - i) DAY)
      );
      SET j = j + 1;
    END WHILE;

    SET i = i + 1;
  END WHILE;
END$$
DELIMITER ;

CALL seed_posts_and_comments();
DROP PROCEDURE seed_posts_and_comments;
