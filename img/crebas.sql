/*==============================================================*/
/* DBMS name:      PostgreSQL 9.x                               */
/* Created on:     2016/11/15 23:34:23                          */
/*==============================================================*/


drop table allstock;

drop table attend;

drop table bk_trans;

drop table bkrecord;

drop table c_trans;

drop table cand_rate;

drop table cand_trans;

drop table candidate;

drop table category;

drop table crecord;

drop table director;

drop table history;

drop table holder;

drop table indexrecord;

drop table pay_result;

drop table sign;

drop table stockaction;

drop table stockrecord;

drop table version;

drop table waverecord;

/*==============================================================*/
/* Table: allstock                                              */
/*==============================================================*/
create table allstock (
   code                 VARCHAR(8)           ,
   name                 VARCHAR(8)           ,
   py                   VARCHAR(4)           ,
   industry             VARCHAR(16)          ,
   pe                   FLOAT8               ,
   pb                   FLOAT8               ,
   totalvalue           FLOAT8               ,
   currency             FLOAT8               ,
   constraint PK_ALLSTOCK primary key (code)
);

/*==============================================================*/
/* Table: attend                                                */
/*==============================================================*/
create table attend (
   code                 VARCHAR(8)           ,
   name                 VARCHAR(8)           ,
   "time"               DATE                 ,
   trans                FLOAT8               ,
   signal               INT4                 ,
   constraint PK_ATTEND primary key (code)
);

/*==============================================================*/
/* Table: bk_trans                                              */
/*==============================================================*/
create table bk_trans (
   id                   serial               ,
   code                 VARCHAR(8)           ,
   name                 VARCHAR(16)          ,
   increase             FLOAT8               ,
   trans                FLOAT8               ,
   "time"               DATE                 ,
   constraint PK_BK_TRANS primary key (id)
);

/*==============================================================*/
/* Table: bkrecord                                              */
/*==============================================================*/
create table bkrecord (
   id                   serial               ,
   code                 VARCHAR(8)           ,
   name                 VARCHAR(16)          ,
   increase             FLOAT8               ,
   date                 DATE                 ,
   "time"               INT4                 ,
   constraint PK_BKRECORD primary key (id)
);

/*==============================================================*/
/* Table: c_trans                                               */
/*==============================================================*/
create table c_trans (
   id                   serial               ,
   code                 VARCHAR(16)          ,
   name                 VARCHAR(16)          ,
   increase             FLOAT8               ,
   trans                FLOAT8               ,
   "time"               DATE                 ,
   constraint PK_C_TRANS primary key (id)
);

/*==============================================================*/
/* Table: cand_rate                                             */
/*==============================================================*/
create table cand_rate (
   id                   serial               ,
   code                 VARCHAR(8)           ,
   name                 VARCHAR(8)           ,
   current              FLOAT8               ,
   a                    FLOAT8               ,
   b                    FLOAT8               ,
   r                    FLOAT8               ,
   increase             FLOAT8               ,
   "time"               DATE                 ,
   constraint PK_CAND_RATE primary key (id)
);

/*==============================================================*/
/* Table: cand_trans                                            */
/*==============================================================*/
create table cand_trans (
   id                   serial               ,
   code                 VARCHAR(8)           ,
   name                 VARCHAR(8)           ,
   current              FLOAT8               ,
   increase             FLOAT8               ,
   trans                FLOAT8               ,
   "time"               DATE                 ,
   constraint PK_CAND_TRANS primary key (id)
);

/*==============================================================*/
/* Table: candidate                                             */
/*==============================================================*/
create table candidate (
   id                   serial               ,
   preflist             VARCHAR(16000)       ,
   "time"               DATE                 ,
   constraint PK_CANDIDATE primary key (id)
);

/*==============================================================*/
/* Table: category                                              */
/*==============================================================*/
create table category (
   code                 VARCHAR(8)           ,
   name                 VARCHAR(16)          ,
   type                 VARCHAR(8)           ,
   content              VARCHAR(8000)        ,
   constraint PK_CATEGORY primary key (code)
);

/*==============================================================*/
/* Table: crecord                                               */
/*==============================================================*/
create table crecord (
   id                   serial               ,
   code                 VARCHAR(16)          ,
   name                 VARCHAR(16)          ,
   increase             FLOAT8               ,
   date                 DATE                 ,
   "time"               INT4                 ,
   constraint PK_CRECORD primary key (id)
);

/*==============================================================*/
/* Table: director                                              */
/*==============================================================*/
create table director (
   id                   serial               ,
   code                 VARCHAR(8)           ,
   name                 VARCHAR(8)           ,
   "time"               DATE                 ,
   price                FLOAT8               ,
   type                 INT2                 ,
   level                INT4                 ,
   flag                 INT2                 ,
   total                INT4                 ,
   arrow                VARCHAR(40)          ,
   constraint PK_DIRECTOR primary key (id)
);

/*==============================================================*/
/* Table: history                                               */
/*==============================================================*/
create table history (
   id                   VARCHAR(13)          ,
   ftime                INT4                 ,
   ltime                INT4                 ,
   record               TEXT                 ,
   type                 VARCHAR(1)           ,
   constraint PK_HISTORY primary key (id)
);

/*==============================================================*/
/* Table: holder                                                */
/*==============================================================*/
create table holder (
   code                 VARCHAR(8)           ,
   name                 VARCHAR(8)           ,
   "time"               DATE                 ,
   trans                FLOAT8               ,
   signal               INT4                 ,
   constraint PK_HOLDER primary key (code)
);

/*==============================================================*/
/* Table: indexrecord                                           */
/*==============================================================*/
create table indexrecord (
   id                   serial               ,
   code                 VARCHAR(8)           ,
   name                 VARCHAR(8)           ,
   close                FLOAT8               ,
   open                 FLOAT8               ,
   current              FLOAT8               ,
   high                 FLOAT8               ,
   low                  FLOAT8               ,
   clmn                 FLOAT8               ,
   money                FLOAT8               ,
   avg                  FLOAT8               ,
   date                 DATE                 ,
   "time"               INT4                 ,
   flag                 VARCHAR(8)           ,
   constraint PK_INDEXRECORD primary key (id)
);

/*==============================================================*/
/* Table: pay_result                                            */
/*==============================================================*/
create table pay_result (
   orderId              VARCHAR(40)          ,
   appId                VARCHAR(40)          ,
   userId               VARCHAR(40)          ,
   payType              INT4                 ,
   resultCode           INT4                 ,
   resultString         VARCHAR(40)          ,
   tradeId              VARCHAR(40)          ,
   amount               FLOAT8               ,
   payTime              DATE                 ,
   sign                 VARCHAR(40)          ,
   comment              VARCHAR(40)          
);

/*==============================================================*/
/* Table: sign                                                  */
/*==============================================================*/
create table sign (
   code                 VARCHAR(8)           ,
   sell                 INT4                 ,
   buy                  INT4                 ,
   prefBuy              FLOAT8               ,
   prefSell             FLOAT8               ,
   current              FLOAT8               ,
   high                 FLOAT8               ,
   low                  FLOAT8               ,
   avg                  FLOAT8               ,
   concept              VARCHAR(100)         ,
   constraint PK_SIGN primary key (code)
);

/*==============================================================*/
/* Table: stockaction                                           */
/*==============================================================*/
create table stockaction (
   id                   serial               ,
   action               INT4                 ,
   ftime                INT4                 ,
   ltime                INT4                 ,
   "time"               DATE                 ,
   queue                TEXT                 ,
   gw                   TEXT                 ,
   type                 INT4                 ,
   content              VARCHAR(32)          ,
   detail               VARCHAR(64)          ,
   flag                 INT4                 ,
   arrow                VARCHAR(40)          ,
   pref                 VARCHAR(300)         ,
   strong               FLOAT8               ,
   dex                  FLOAT8               ,
   constraint PK_STOCKACTION primary key (id)
);

/*==============================================================*/
/* Table: stockrecord                                           */
/*==============================================================*/
create table stockrecord (
   id                   serial               ,
   code                 VARCHAR(8)           ,
   name                 VARCHAR(8)           ,
   close                FLOAT8               ,
   open                 FLOAT8               ,
   current              FLOAT8               ,
   high                 FLOAT8               ,
   low                  FLOAT8               ,
   clmn                 FLOAT8               ,
   money                FLOAT8               ,
   avg                  FLOAT8               ,
   date                 DATE                 ,
   "time"               INT4                 ,
   flag                 VARCHAR(8)           ,
   constraint PK_STOCKRECORD primary key (id)
);

/*==============================================================*/
/* Table: version                                               */
/*==============================================================*/
create table version (
   id                   serial               ,
   ver                  INT4                 ,
   bcheck               INT2                 ,
   title                VARCHAR(80)          ,
   content              VARCHAR(1000)        ,
   url                  VARCHAR(80)          ,
   type                 INT4                 ,
   extra                VARCHAR(16000)       ,
   constraint PK_VERSION primary key (id)
);

/*==============================================================*/
/* Table: waverecord                                            */
/*==============================================================*/
create table waverecord (
   id                   serial               ,
   code                 VARCHAR(8)           ,
   dt                   DATE                 ,
   wv                   TEXT                 ,
   gw                   TEXT                 ,
   arrow                VARCHAR(40)          ,
   constraint PK_WAVERECORD primary key (id)
);

