CREATE TABLE tt_content (
	tx_dalleimage_prompt_subject varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_description varchar(500) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_illustration varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_style varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_artworks varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_artists varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_lighting varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_film_type varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_camera_position varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_camera_lenses varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_camera_shot varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_colors varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_emotion varchar(255) DEFAULT '' NOT NULL,
	tx_dalleimage_prompt_composition varchar(255) DEFAULT '' NOT NULL,
);

CREATE TABLE sys_file_reference (
	tx_dalleimage_prompt varchar(1000) DEFAULT '' NOT NULL,
);
