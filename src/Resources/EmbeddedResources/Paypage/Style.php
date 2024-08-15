<?php

namespace UnzerSDK\Resources\EmbeddedResources\Paypage;

use UnzerSDK\Resources\AbstractUnzerResource;

class Style extends AbstractUnzerResource
{
    protected ?string $fontFamily = null;
    protected ?string $buttonColor = null;
    protected ?string $primaryTextColor = null;
    protected ?string $linkColor = null;
    protected ?string $backgroundColor = null;
    protected ?string $cornerRadius = null;
    protected ?bool $shadows = null;
    protected ?bool $hideUnzerLogo = null;

    public function getFontFamily(): ?string
    {
        return $this->fontFamily;
    }

    public function setFontFamily(?string $fontFamily): Style
    {
        $this->fontFamily = $fontFamily;
        return $this;
    }

    public function getButtonColor(): ?string
    {
        return $this->buttonColor;
    }

    public function setButtonColor(?string $buttonColor): Style
    {
        $this->buttonColor = $buttonColor;
        return $this;
    }

    public function getPrimaryTextColor(): ?string
    {
        return $this->primaryTextColor;
    }

    public function setPrimaryTextColor(?string $primaryTextColor): Style
    {
        $this->primaryTextColor = $primaryTextColor;
        return $this;
    }

    public function getLinkColor(): ?string
    {
        return $this->linkColor;
    }

    public function setLinkColor(?string $linkColor): Style
    {
        $this->linkColor = $linkColor;
        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): Style
    {
        $this->backgroundColor = $backgroundColor;
        return $this;
    }

    public function getCornerRadius(): ?string
    {
        return $this->cornerRadius;
    }

    public function setCornerRadius(?string $cornerRadius): Style
    {
        $this->cornerRadius = $cornerRadius;
        return $this;
    }

    public function getShadows(): ?bool
    {
        return $this->shadows;
    }

    public function setShadows(?bool $shadows): Style
    {
        $this->shadows = $shadows;
        return $this;
    }

    public function getHideUnzerLogo(): ?bool
    {
        return $this->hideUnzerLogo;
    }

    public function setHideUnzerLogo(?bool $hideUnzerLogo): Style
    {
        $this->hideUnzerLogo = $hideUnzerLogo;
        return $this;
    }
}