/**
 * Block Editor Integration for WP Referral Link Maker
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/admin/js
 */

(function(wp) {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
    const { PanelBody, Button, Spinner, Notice } = wp.components;
    const { Component, Fragment } = wp.element;
    const { withSelect } = wp.data;
    const { __ } = wp.i18n;

    /**
     * Referral Link Sidebar Component
     */
    class ReferralLinkSidebar extends Component {
        constructor(props) {
            super(props);

            this.state = {
                suggestedLinks: [],
                loading: false,
                error: null,
                showPreview: false,
                previewContent: '',
            };

            this.handleSuggestLinks = this.handleSuggestLinks.bind(this);
            this.handlePreviewLinks = this.handlePreviewLinks.bind(this);
            this.handleApplyLinks = this.handleApplyLinks.bind(this);
            this.handleClosePreview = this.handleClosePreview.bind(this);
        }

        /**
         * Suggest links based on current post content
         */
        handleSuggestLinks() {
            const { postContent } = this.props;

            if (!postContent || postContent.trim() === '') {
                this.setState({
                    error: __('Please add some content to your post first.', 'wp-referral-link-maker'),
                });
                return;
            }

            this.setState({ loading: true, error: null });

            jQuery.ajax({
                url: wpRlmBlockEditor.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_rlm_suggest_links',
                    nonce: wpRlmBlockEditor.nonce,
                    content: postContent,
                },
                success: (response) => {
                    if (response.success) {
                        this.setState({
                            suggestedLinks: response.data.links,
                            loading: false,
                            error: null,
                        });
                    } else {
                        this.setState({
                            loading: false,
                            error: response.data.message || wpRlmBlockEditor.i18n.error,
                        });
                    }
                },
                error: () => {
                    this.setState({
                        loading: false,
                        error: wpRlmBlockEditor.i18n.error,
                    });
                },
            });
        }

        /**
         * Preview content with referral links
         */
        handlePreviewLinks() {
            const { postContent } = this.props;

            if (!postContent || postContent.trim() === '') {
                this.setState({
                    error: __('Please add some content to your post first.', 'wp-referral-link-maker'),
                });
                return;
            }

            this.setState({ loading: true, error: null });

            jQuery.ajax({
                url: wpRlmBlockEditor.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_rlm_preview_links',
                    nonce: wpRlmBlockEditor.nonce,
                    content: postContent,
                },
                success: (response) => {
                    if (response.success) {
                        this.setState({
                            previewContent: response.data.content,
                            showPreview: true,
                            loading: false,
                            error: null,
                        });
                    } else {
                        this.setState({
                            loading: false,
                            error: response.data.message || wpRlmBlockEditor.i18n.error,
                        });
                    }
                },
                error: () => {
                    this.setState({
                        loading: false,
                        error: wpRlmBlockEditor.i18n.error,
                    });
                },
            });
        }

        /**
         * Apply suggested links to post content
         */
        handleApplyLinks() {
            const { previewContent } = this.state;
            
            if (!previewContent) {
                return;
            }

            // Update the post content with the preview content
            wp.data.dispatch('core/editor').editPost({
                content: previewContent,
            });

            this.setState({
                showPreview: false,
                previewContent: '',
            });
        }

        /**
         * Close preview modal
         */
        handleClosePreview() {
            this.setState({
                showPreview: false,
                previewContent: '',
            });
        }

        render() {
            const { suggestedLinks, loading, error, showPreview, previewContent } = this.state;

            return (
                <Fragment>
                    <PluginSidebarMoreMenuItem target="wp-rlm-sidebar">
                        {wpRlmBlockEditor.i18n.title}
                    </PluginSidebarMoreMenuItem>

                    <PluginSidebar
                        name="wp-rlm-sidebar"
                        title={wpRlmBlockEditor.i18n.title}
                    >
                        <PanelBody>
                            {error && (
                                <Notice status="error" isDismissible={false}>
                                    {error}
                                </Notice>
                            )}

                            <div className="wp-rlm-actions">
                                <Button
                                    isPrimary
                                    onClick={this.handleSuggestLinks}
                                    disabled={loading}
                                >
                                    {loading ? <Spinner /> : wpRlmBlockEditor.i18n.suggestLinks}
                                </Button>

                                <Button
                                    isSecondary
                                    onClick={this.handlePreviewLinks}
                                    disabled={loading}
                                    style={{ marginTop: '10px' }}
                                >
                                    {loading ? <Spinner /> : wpRlmBlockEditor.i18n.previewLinks}
                                </Button>
                            </div>

                            {suggestedLinks.length > 0 && (
                                <div className="wp-rlm-suggested-links">
                                    <h3>{wpRlmBlockEditor.i18n.suggestedLinks}</h3>
                                    <ul>
                                        {suggestedLinks.map((link) => (
                                            <li key={link.id} className="wp-rlm-link-item">
                                                <strong>{link.keyword}</strong>
                                                <div className="wp-rlm-link-meta">
                                                    <span className="wp-rlm-occurrences">
                                                        {link.occurrences} {link.occurrences === 1 ? 'occurrence' : 'occurrences'}
                                                    </span>
                                                    {link.context && (
                                                        <p className="wp-rlm-context">{link.context}</p>
                                                    )}
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </PanelBody>
                    </PluginSidebar>

                    {showPreview && (
                        <div className="wp-rlm-preview-modal">
                            <div className="wp-rlm-preview-content">
                                <div className="wp-rlm-preview-header">
                                    <h2>{wpRlmBlockEditor.i18n.preview}</h2>
                                    <Button
                                        isLink
                                        onClick={this.handleClosePreview}
                                        className="wp-rlm-close-preview"
                                    >
                                        {wpRlmBlockEditor.i18n.closePreview}
                                    </Button>
                                </div>
                                <div 
                                    className="wp-rlm-preview-body"
                                    dangerouslySetInnerHTML={{ __html: previewContent }}
                                />
                                <div className="wp-rlm-preview-footer">
                                    <Button
                                        isPrimary
                                        onClick={this.handleApplyLinks}
                                    >
                                        {wpRlmBlockEditor.i18n.applyLinks}
                                    </Button>
                                    <Button
                                        isSecondary
                                        onClick={this.handleClosePreview}
                                    >
                                        {wpRlmBlockEditor.i18n.closePreview}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    )}
                </Fragment>
            );
        }
    }

    // Connect component to WordPress data
    const ReferralLinkSidebarWithSelect = withSelect((select) => {
        const editor = select('core/editor');
        return {
            postContent: editor.getEditedPostContent(),
        };
    })(ReferralLinkSidebar);

    // Register the plugin
    registerPlugin('wp-referral-link-maker', {
        render: ReferralLinkSidebarWithSelect,
        icon: 'admin-links',
    });

})(window.wp);
