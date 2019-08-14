# teamup-server

CREATE TABLE public.tu_link_user_team
(
    id integer NOT NULL DEFAULT nextval('tu_link_user_team_id_seq'::regclass),
    user_id integer NOT NULL,
    team_id integer NOT NULL,
    status smallint NOT NULL,
    remark character varying(255) COLLATE pg_catalog."default" DEFAULT NULL::character varying,
    CONSTRAINT tu_link_user_team_user_id_check CHECK (user_id > 0),
    CONSTRAINT tu_link_user_team_team_id_check CHECK (team_id > 0)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.tu_link_user_team
    OWNER to postgres;


CREATE TABLE public.tu_message
(
    id integer NOT NULL DEFAULT nextval('tu_message_id_seq'::regclass),
    from_id integer NOT NULL,
    to_id integer NOT NULL,
    status smallint NOT NULL,
    msg_time timestamp(0) without time zone NOT NULL,
    content character varying(512) COLLATE pg_catalog."default" NOT NULL,
    CONSTRAINT tu_message_from_id_check CHECK (from_id > 0),
    CONSTRAINT tu_message_to_id_check CHECK (to_id > 0)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.tu_message
    OWNER to postgres;


CREATE TABLE public.tu_session
(
    id integer NOT NULL DEFAULT nextval('tu_session_id_seq'::regclass),
    user_id integer NOT NULL,
    last_time timestamp(0) without time zone NOT NULL,
    session_id character varying(60) COLLATE pg_catalog."default" NOT NULL,
    dev_uid character varying(100) COLLATE pg_catalog."default" DEFAULT NULL::character varying,
    dev_type character varying(20) COLLATE pg_catalog."default" DEFAULT NULL::character varying,
    dev_model character varying(50) COLLATE pg_catalog."default" DEFAULT NULL::character varying,
    token text COLLATE pg_catalog."default",
    CONSTRAINT tu_session_user_id_check CHECK (user_id > 0)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.tu_session
    OWNER to postgres;


CREATE TABLE public.tu_signup
(
    id integer NOT NULL DEFAULT nextval('tu_signup_id_seq'::regclass),
    activate_id character varying(64) COLLATE pg_catalog."default" NOT NULL,
    username character varying(64) COLLATE pg_catalog."default" NOT NULL,
    password character varying(64) COLLATE pg_catalog."default" NOT NULL,
    name_type character varying(10) COLLATE pg_catalog."default" NOT NULL,
    reg_time timestamp(0) without time zone NOT NULL,
    status smallint NOT NULL
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.tu_signup
    OWNER to postgres;


CREATE TABLE public.tu_team
(
    id integer NOT NULL DEFAULT nextval('tu_team_id_seq'::regclass),
    author integer NOT NULL,
    category smallint NOT NULL,
    time_begin timestamp(0) without time zone NOT NULL,
    time_end timestamp(0) without time zone NOT NULL,
    need_review smallint NOT NULL,
    dp_self smallint NOT NULL,
    dp_other smallint NOT NULL,
    create_time timestamp(0) without time zone NOT NULL,
    status smallint NOT NULL,
    people character varying(32) COLLATE pg_catalog."default" NOT NULL,
    title character varying(255) COLLATE pg_catalog."default" NOT NULL,
    location character varying(255) COLLATE pg_catalog."default" NOT NULL,
    "desc" character varying(1024) COLLATE pg_catalog."default" NOT NULL DEFAULT ''::character varying,
    CONSTRAINT tu_team_author_check CHECK (author > 0)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.tu_team
    OWNER to postgres;


CREATE TABLE public.tu_user
(
    id integer NOT NULL DEFAULT nextval('tu_user_id_seq'::regclass),
    username character varying(60) COLLATE pg_catalog."default" NOT NULL,
    password character varying(50) COLLATE pg_catalog."default" NOT NULL,
    level integer NOT NULL,
    first_name character varying(30) COLLATE pg_catalog."default" DEFAULT NULL::character varying,
    last_name character varying(30) COLLATE pg_catalog."default" DEFAULT NULL::character varying,
    email character varying(60) COLLATE pg_catalog."default" NOT NULL,
    mobile character varying(20) COLLATE pg_catalog."default" DEFAULT NULL::character varying,
    sex smallint NOT NULL,
    birthday date,
    is_active smallint NOT NULL,
    reg_time timestamp(0) without time zone NOT NULL,
    "desc" character varying(100) COLLATE pg_catalog."default" DEFAULT NULL::character varying,
    photo_url character varying(255) COLLATE pg_catalog."default" DEFAULT NULL::character varying,
    source character varying(16) COLLATE pg_catalog."default" NOT NULL
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.tu_user
    OWNER to postgres;


CREATE TABLE public.tu_user_session
(
    id integer NOT NULL DEFAULT nextval('tu_user_session_id_seq'::regclass),
    user_id integer NOT NULL,
    token character varying(128) COLLATE pg_catalog."default" NOT NULL,
    last_time timestamp without time zone NOT NULL,
    expired integer NOT NULL,
    dev_type character varying(32) COLLATE pg_catalog."default" NOT NULL,
    dev_model character varying(128) COLLATE pg_catalog."default",
    dev_uid character varying(128) COLLATE pg_catalog."default",
    CONSTRAINT bip_user_session_pkey PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.tu_user_session
    OWNER to postgres;
