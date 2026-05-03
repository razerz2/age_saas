<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

DB::table('invoices')->where('id','a1af2778-4dc0-4267-986b-f4f5f5178492')->update(['status'=>'pending','paid_at'=>null,'updated_at'=>now()]);
DB::table('subscriptions')->where('id','a1af276d-9c3f-4102-a20b-27697d98e964')->update(['status'=>'active','updated_at'=>now()]);

DB::table('invoices')->where('id','a1afc759-148c-4d6b-bd50-7144920c5e26')->update(['status'=>'pending','paid_at'=>null,'updated_at'=>now()]);
DB::table('subscriptions')->where('id','a1ade1a2-8eb1-4b8c-b424-f2355f0dd457')->update(['status'=>'active','updated_at'=>now()]);

DB::table('invoices')->where('id','a1afc439-7210-4412-9d13-2601d05938b0')->update(['status'=>'pending','paid_at'=>null,'updated_at'=>now()]);
DB::table('subscriptions')->where('id','a1af2730-8384-4189-a175-93084c3ad789')->update(['status'=>'active','updated_at'=>now()]);

DB::table('invoices')->where('id','a1afdd2c-1a17-48b5-be6b-49e9badf89ac')->update(['status'=>'pending','paid_at'=>null,'updated_at'=>now()]);
DB::table('subscriptions')->where('id','a1afc65c-9372-4a04-81ce-10faea1947a7')->update(['status'=>'past_due','updated_at'=>now()]);

DB::table('tenants')->where('id','45340052-01ec-4a19-b46c-b04cc5ad7914')->update(['status'=>'active','suspended_at'=>null,'updated_at'=>now()]);

echo "restored\n";
