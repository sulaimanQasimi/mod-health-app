<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SanitizeHtmlTest extends TestCase
{
    public function test_it_strips_script_tags_and_event_handlers(): void
    {
        $dirty = '<p>OK</p><script>alert(1)</script><img src=x onerror="alert(1)"><a href="javascript:alert(1)">x</a>';

        $clean = sanitize_html($dirty);

        $this->assertStringContainsString('<p>OK</p>', $clean);
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', strtolower($clean));
        $this->assertStringNotContainsString('<img', $clean);
    }

    public function test_it_keeps_basic_formatting_tags(): void
    {
        $html = '<p><strong>Bold</strong> and <em>italic</em></p><ul><li>One</li></ul>';

        $this->assertSame($html, sanitize_html($html));
    }

    public function test_safe_sort_helpers_reject_injection_payloads(): void
    {
        $this->assertSame('desc', safe_sort_order("desc; drop table users--"));
        $this->assertSame('asc', safe_sort_order('ASC'));
        $this->assertSame('created_at', safe_sort_column('id); DROP TABLE users--', ['id', 'created_at'], 'created_at'));
        $this->assertSame('id', safe_sort_column('id', ['id', 'created_at'], 'created_at'));
    }

    public function test_lab_report_xss_payload_is_neutralized_for_print_views(): void
    {
        $payload = '<p>Result</p><script>document.cookie</script><b onmouseover=alert(1)>x</b>';
        $clean = sanitize_html($payload);

        $this->assertStringContainsString('<p>Result</p>', $clean);
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringNotContainsString('onmouseover', $clean);
        $this->assertStringContainsString('<b>x</b>', $clean);
    }
}

