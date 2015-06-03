create table sm_cashconverter_records (
id integer unsigned not null primary key auto_increment,
userid varchar(255) not null,
username varchar(255) not null,
credits_requested_to_convert integer unsigned not null,
percentage_allocated_to_ads varchar(4) not null,
credits_allocated_to_ads integer unsigned not null,
cash_conversion_per_credit decimal(9,3) not null,
total_cash_requested decimal(9,3) not null,
approved varchar(4) not null default 'no'
);

create table sm_cashconverter_settings (
id integer unsigned not null primary key auto_increment,
cash_converter_form_html longtext not null,
cash_rate_per_surf_credit decimal(9,3) not null default '0.001',
percent_credits_forced_for_ads varchar(4) not null default '25',
minimum_credits_allowed_to_request integer unsigned not null default '1',
maximum_credits_allowed_to_request integer unsigned not null default '10000'
);

insert into sm_cashconverter_settings (id) values (1);

insert into oto_adminmenu (`id`, `menu_label`, `menu_url`, `menu_target`, `menu_parent`, `menu_order`, `f`, `filename`) VALUES ('2370', 'Credits-To-Cash Converter', 'admin.php?f=sm_converter', '_top', '2300', '9', 'sm_converter', 'sm_cashconverter_admin.php');

-----------------------------------------------------------------------------------

Add Members Area Page from LFMVM Admin area: Site Design -> Content Pages
So you can decide which membership levels to allow access to the mod.

In the page content part of the form, just paste the below and then save by clicking the
"Update Template" button.

If members try to visit the file directly, it will just forward them to the main members page.

<center><iframe width="600" height="1000" src="sm_cashconverter.php" frameborder="0" scrolling="auto" allowtransparency="true"></iframe></center>


