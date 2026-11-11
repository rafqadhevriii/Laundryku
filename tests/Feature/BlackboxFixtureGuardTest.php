<?php
namespace Tests\Feature;

use App\Support\Blackbox\{Fixtures,SafetyException};
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BlackboxFixtureGuardTest extends TestCase
{
    public function test_direct_fixture_calls_are_refused_before_any_sql_in_phpunit_environment(): void
    {
        $queries=[];DB::listen(function($query)use(&$queries){$queries[]=$query->sql;});
        foreach(['initial','advanced'] as $method){
            try{(new Fixtures)->$method();$this->fail('Direct fixture writes were accepted');}
            catch(SafetyException $e){$this->assertStringContainsString('scripts/blackbox.php',$e->getMessage());}
        }
        $this->assertSame([],$queries);
    }
}
